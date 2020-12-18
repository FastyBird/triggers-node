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

use FastyBird\ModulesMetadata;
use FastyBird\ModulesMetadata\Loaders as ModulesMetadataLoaders;
use FastyBird\ModulesMetadata\Schemas as ModulesMetadataSchemas;
use FastyBird\RabbitMqPlugin\Consumers as RabbitMqPluginConsumers;
use FastyBird\RabbitMqPlugin\Publishers as RabbitMqPluginPublishers;
use FastyBird\TriggersModule\Entities as TriggersModuleEntities;
use FastyBird\TriggersModule\Models as TriggersModuleModels;
use FastyBird\TriggersModule\Queries as TriggersModuleQueries;
use FastyBird\TriggersModule\Types as TriggersModuleTypes;
use FastyBird\TriggersNode\Exceptions;
use Psr\Log;
use Throwable;

/**
 * Device property command messages consumer
 *
 * @package        FastyBird:TriggersNode!
 * @subpackage     Consumers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class DevicePropertyMessageHandler implements RabbitMqPluginConsumers\IMessageHandler
{

	use TPropertyDataMessageHandler;

	/** @var RabbitMqPluginPublishers\IRabbitMqPublisher */
	protected RabbitMqPluginPublishers\IRabbitMqPublisher $rabbitMqPublisher;

	/** @var TriggersModuleModels\Conditions\IConditionRepository */
	private TriggersModuleModels\Conditions\IConditionRepository $conditionRepository;

	/** @var TriggersModuleModels\Conditions\IConditionsManager */
	private TriggersModuleModels\Conditions\IConditionsManager $conditionsManager;

	/** @var ModulesMetadataLoaders\ISchemaLoader */
	private ModulesMetadataLoaders\ISchemaLoader $schemaLoader;

	/** @var ModulesMetadataSchemas\IValidator */
	private ModulesMetadataSchemas\IValidator $validator;

	/** @var Log\LoggerInterface */
	protected Log\LoggerInterface $logger;

	public function __construct(
		TriggersModuleModels\Conditions\IConditionRepository $conditionRepository,
		TriggersModuleModels\Conditions\IConditionsManager $conditionsManager,
		ModulesMetadataLoaders\ISchemaLoader $schemaLoader,
		ModulesMetadataSchemas\IValidator $validator,
		RabbitMqPluginPublishers\IRabbitMqPublisher $rabbitMqPublisher,
		Log\LoggerInterface $logger
	) {
		$this->conditionRepository = $conditionRepository;
		$this->conditionsManager = $conditionsManager;

		$this->schemaLoader = $schemaLoader;
		$this->validator = $validator;
		$this->rabbitMqPublisher = $rabbitMqPublisher;
		$this->logger = $logger;
	}

	/**
	 * {@inheritDoc}
	 */
	public function process(
		string $routingKey,
		string $origin,
		string $payload
	): bool {
		$schema = $this->getSchema($routingKey, $origin);

		if ($schema === null) {
			return true;
		}

		try {
			$message = $this->validator->validate($payload, $schema);

		} catch (Throwable $ex) {
			$this->logger->error('[FB:NODE:CONSUMER] ' . $ex->getMessage(), [
				'exception' => [
					'message' => $ex->getMessage(),
					'code'    => $ex->getCode(),
				],
			]);

			return true;
		}

		if ($routingKey === ModulesMetadata\Constants::MESSAGE_BUS_DEVICES_PROPERTY_DELETED_ENTITY_ROUTING_KEY) {
			$this->clearProperties(
				$message->offsetGet('device'),
				$message->offsetGet('property')
			);

		} elseif ($routingKey === ModulesMetadata\Constants::MESSAGE_BUS_DEVICES_PROPERTY_UPDATED_ENTITY_ROUTING_KEY) {
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
		if ($origin === ModulesMetadata\Constants::MODULE_DEVICES_ORIGIN) {
			switch ($routingKey) {
				case ModulesMetadata\Constants::MESSAGE_BUS_DEVICES_PROPERTY_DELETED_ENTITY_ROUTING_KEY:
				case ModulesMetadata\Constants::MESSAGE_BUS_DEVICES_PROPERTY_UPDATED_ENTITY_ROUTING_KEY:
					return $this->schemaLoader->load(ModulesMetadata\Constants::RESOURCES_FOLDER . '/schemas/devices-module/entity.device.property.json');
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
		$findQuery = new TriggersModuleQueries\FindConditionsQuery();
		$findQuery->forDeviceProperty($device, $property);

		$conditions = $this->conditionRepository->findAllBy($findQuery, TriggersModuleEntities\Conditions\DevicePropertyCondition::class);

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

		$findQuery = new TriggersModuleQueries\FindConditionsQuery();
		$findQuery->forDeviceProperty($device, $property);

		$conditions = $this->conditionRepository->findAllBy($findQuery, TriggersModuleEntities\Conditions\DevicePropertyCondition::class);

		/** @var TriggersModuleEntities\Conditions\DevicePropertyCondition $condition */
		foreach ($conditions as $condition) {
			if (
				$condition->getOperator()->equalsValue(TriggersModuleTypes\ConditionOperatorType::STATE_VALUE_EQUAL)
				&& $condition->getOperand() === $value
			) {
				$this->processCondition($condition);
			}
		}
	}

}
