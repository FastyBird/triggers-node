<?php declare(strict_types = 1);

/**
 * DevicePropertyMessageHandler.php
 *
 * @license        More in license.md
 * @copyright      https://fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:TriggersNode!
 * @subpackage     Consumers
 * @since          0.1.0
 *
 * @date           05.04.20
 */

namespace FastyBird\TriggersNode\Consumers;

use FastyBird\NodeExchange\Consumers as NodeExchangeConsumers;
use FastyBird\NodeExchange\Publishers as NodeExchangePublishers;
use FastyBird\NodeMetadata;
use FastyBird\NodeMetadata\Loaders as NodeMetadataLoaders;
use FastyBird\TriggersNode;
use FastyBird\TriggersNode\Entities;
use FastyBird\TriggersNode\Exceptions;
use FastyBird\TriggersNode\Models;
use FastyBird\TriggersNode\Queries;
use FastyBird\TriggersNode\Types;
use Nette\Utils;
use Psr\Log;

/**
 * Device property command messages consumer
 *
 * @package        FastyBird:TriggersNode!
 * @subpackage     Consumers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class DevicePropertyMessageHandler implements NodeExchangeConsumers\IMessageHandler
{

	use TPropertyDataMessageHandler;

	/** @var NodeExchangePublishers\IRabbitMqPublisher */
	protected $rabbitMqPublisher;

	/** @var Log\LoggerInterface */
	protected $logger;

	/** @var Models\Conditions\IConditionRepository */
	private $conditionRepository;

	/** @var Models\Conditions\IConditionsManager */
	private $conditionsManager;

	/** @var NodeMetadataLoaders\ISchemaLoader */
	private $schemaLoader;

	public function __construct(
		Models\Conditions\IConditionRepository $conditionRepository,
		Models\Conditions\IConditionsManager $conditionsManager,
		NodeMetadataLoaders\ISchemaLoader $schemaLoader,
		NodeExchangePublishers\IRabbitMqPublisher $rabbitMqPublisher,
		Log\LoggerInterface $logger
	) {
		$this->conditionRepository = $conditionRepository;
		$this->conditionsManager = $conditionsManager;

		$this->schemaLoader = $schemaLoader;
		$this->rabbitMqPublisher = $rabbitMqPublisher;
		$this->logger = $logger;
	}

	/**
	 * {@inheritDoc}
	 */
	public function process(
		string $routingKey,
		Utils\ArrayHash $message
	): bool {
		if ($routingKey === TriggersNode\Constants::RABBIT_MQ_DEVICES_PROPERTY_DELETED_ENTITY_ROUTING_KEY) {
			$this->clearProperties(
				$message->offsetGet('device'),
				$message->offsetGet('property')
			);

		} elseif ($routingKey === TriggersNode\Constants::RABBIT_MQ_DEVICES_PROPERTY_UPDATED_ENTITY_ROUTING_KEY) {
			// Only not pending messages will be processed
			if (
				$message->offsetExists('pending')
				&& $message->offsetGet('pending') === false
				&& $message->offsetExists('value')
			) {
				$this->processDeviceConditions(
					$message->offsetGet('device'),
					$message->offsetGet('property'),
					$message->offsetGet('value'),
					$message->offsetExists('previous_value') ? $message->offsetGet('previous_value') : null,
					$message->offsetGet('datatype')
				);
			}

		} else {
			throw new Exceptions\InvalidStateException('Unknown routing key');
		}

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getSchema(string $routingKey, string $origin): ?string
	{
		if ($origin === TriggersNode\Constants::NODE_DEVICES_ORIGIN) {
			switch ($routingKey) {
				case TriggersNode\Constants::RABBIT_MQ_DEVICES_PROPERTY_DELETED_ENTITY_ROUTING_KEY:
				case TriggersNode\Constants::RABBIT_MQ_DEVICES_PROPERTY_UPDATED_ENTITY_ROUTING_KEY:
					return $this->schemaLoader->load(NodeMetadata\Constants::RESOURCES_FOLDER . '/schemas/devices-node/entity.device.property.json');
			}
		}

		return null;
	}

	/**
	 * @param string $device
	 * @param string $property
	 *
	 * @return void
	 */
	private function clearProperties(string $device, string $property): void
	{
		/** @var Queries\FindConditionsQuery<Entities\Conditions\DevicePropertyCondition> $findQuery */
		$findQuery = new Queries\FindConditionsQuery();
		$findQuery->forDeviceProperty($device, $property);

		$conditions = $this->conditionRepository->findAllBy($findQuery, Entities\Conditions\DevicePropertyCondition::class);

		foreach ($conditions as $condition) {
			$this->conditionsManager->delete($condition);
		}

		$this->logger->info('[CONSUMER] Successfully consumed device property data message');
	}

	/**
	 * @param string $device
	 * @param string $property
	 * @param mixed $value
	 * @param mixed|null $previousValue
	 * @param string|null $datatype
	 *
	 * @return void
	 */
	private function processDeviceConditions(
		string $device,
		string $property,
		$value,
		$previousValue = null,
		?string $datatype = null
	): void {
		$value = $this->formatValue($value, $datatype);
		$previousValue = $this->formatValue($previousValue, $datatype);

		// Previous value is same as current, skipping
		if ($previousValue !== null && $value === $previousValue) {
			return;
		}

		$findQuery = new Queries\FindConditionsQuery();
		$findQuery->forDeviceProperty($device, $property);

		$conditions = $this->conditionRepository->findAllBy($findQuery, Entities\Conditions\DevicePropertyCondition::class);

		/** @var Entities\Conditions\DevicePropertyCondition $condition */
		foreach ($conditions as $condition) {
			if (
				$condition->getOperator()->equalsValue(Types\ConditionOperatorType::STATE_VALUE_EQUAL)
				&& $condition->getOperand() === $value
			) {
				$this->processCondition($condition);
			}
		}
	}

}
