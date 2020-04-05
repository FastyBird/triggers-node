<?php declare(strict_types = 1);

/**
 * DevicePropertyMessageHandler.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:TriggersNode!
 * @subpackage     Consumers
 * @since          0.1.0
 *
 * @date           05.04.20
 */

namespace FastyBird\TriggersNode\Consumers;

use FastyBird\NodeLibs\Consumers as NodeLibsConsumers;
use FastyBird\NodeLibs\Helpers as NodeLibsHelpers;
use FastyBird\TriggersNode;
use FastyBird\TriggersNode\Entities;
use FastyBird\TriggersNode\Exceptions;
use FastyBird\TriggersNode\Models;
use FastyBird\TriggersNode\Queries;
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
final class DevicePropertyMessageHandler implements NodeLibsConsumers\IMessageHandler
{

	/** @var Models\Conditions\IConditionRepository */
	private $conditionRepository;

	/** @var Models\Conditions\IConditionsManager */
	private $conditionsManager;

	/** @var NodeLibsHelpers\ISchemaLoader */
	private $schemaLoader;

	/** @var Log\LoggerInterface */
	private $logger;

	public function __construct(
		Models\Conditions\IConditionRepository $conditionRepository,
		Models\Conditions\IConditionsManager $conditionsManager,
		NodeLibsHelpers\ISchemaLoader $schemaLoader,
		Log\LoggerInterface $logger
	) {
		$this->conditionRepository = $conditionRepository;
		$this->conditionsManager = $conditionsManager;

		$this->schemaLoader = $schemaLoader;
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

		} elseif ($routingKey === TriggersNode\Constants::RABBIT_MQ_DEVICES_PROPERTIES_DATA_ROUTING_KEY) {
			// TODO: Handle trigger actions
			return true;

		} else {
			throw new Exceptions\InvalidStateException('Unknown routing key');
		}

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getSchema(string $routingKey): string
	{
		switch ($routingKey) {
			case TriggersNode\Constants::RABBIT_MQ_DEVICES_PROPERTY_DELETED_ENTITY_ROUTING_KEY:
				return $this->schemaLoader->load('entity.device.property.json');

			case TriggersNode\Constants::RABBIT_MQ_DEVICES_PROPERTIES_DATA_ROUTING_KEY:
				return $this->schemaLoader->load('data.device.property.json');

			default:
				throw new Exceptions\InvalidStateException('Unknown routing key');
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRoutingKeys(bool $binding = false): array
	{
		if ($binding) {
			return TriggersNode\Constants::RABBIT_MQ_DEVICES_ENTITIES_ROUTING_KEY;
		}

		return [
			TriggersNode\Constants::RABBIT_MQ_DEVICES_PROPERTY_DELETED_ENTITY_ROUTING_KEY,

			TriggersNode\Constants::RABBIT_MQ_DEVICES_PROPERTIES_DATA_ROUTING_KEY,
		];
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

		$this->clearConditions($findQuery);

		$this->logger->info('[CONSUMER] Successfully consumed device property data message');
	}

	/**
	 * @param Queries\FindConditionsQuery $findQuery
	 *
	 * @return void
	 *
	 * @phpstan-template T of Entities\Conditions\DevicePropertyCondition
	 * @phpstan-param    Queries\FindConditionsQuery<T> $findQuery
	 */
	private function clearConditions(Queries\FindConditionsQuery $findQuery): void
	{
		$conditions = $this->conditionRepository->findAllBy($findQuery, Entities\Conditions\DevicePropertyCondition::class);

		foreach ($conditions as $condition) {
			$this->conditionsManager->delete($condition);
		}
	}

}
