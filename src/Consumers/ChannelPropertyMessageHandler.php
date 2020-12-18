<?php declare(strict_types = 1);

/**
 * ChannelPropertyMessageHandler.php
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
 * Channel property command messages consumer
 *
 * @package        FastyBird:TriggersNode!
 * @subpackage     Consumers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class ChannelPropertyMessageHandler implements RabbitMqPluginConsumers\IMessageHandler
{

	use TPropertyDataMessageHandler;

	/** @var RabbitMqPluginPublishers\IRabbitMqPublisher */
	protected RabbitMqPluginPublishers\IRabbitMqPublisher $rabbitMqPublisher;

	/** @var TriggersModuleModels\Triggers\ITriggerRepository */
	private TriggersModuleModels\Triggers\ITriggerRepository $triggerRepository;

	/** @var TriggersModuleModels\Triggers\ITriggersManager */
	private TriggersModuleModels\Triggers\ITriggersManager $triggersManager;

	/** @var TriggersModuleModels\Actions\IActionRepository */
	private TriggersModuleModels\Actions\IActionRepository $actionRepository;

	/** @var TriggersModuleModels\Actions\IActionsManager */
	private TriggersModuleModels\Actions\IActionsManager $actionsManager;

	/** @var TriggersModuleModels\Conditions\IConditionRepository */
	private $conditionRepository;

	/** @var TriggersModuleModels\Conditions\IConditionsManager */
	private TriggersModuleModels\Conditions\IConditionsManager $conditionsManager;

	/** @var ModulesMetadataLoaders\ISchemaLoader */
	private ModulesMetadataLoaders\ISchemaLoader $schemaLoader;

	/** @var ModulesMetadataSchemas\IValidator */
	private ModulesMetadataSchemas\IValidator $validator;

	/** @var Log\LoggerInterface */
	protected Log\LoggerInterface $logger;

	public function __construct(
		TriggersModuleModels\Triggers\ITriggerRepository $triggerRepository,
		TriggersModuleModels\Triggers\ITriggersManager $triggersManager,
		TriggersModuleModels\Actions\IActionRepository $actionRepository,
		TriggersModuleModels\Actions\IActionsManager $actionsManager,
		TriggersModuleModels\Conditions\IConditionRepository $conditionRepository,
		TriggersModuleModels\Conditions\IConditionsManager $conditionsManager,
		ModulesMetadataLoaders\ISchemaLoader $schemaLoader,
		ModulesMetadataSchemas\IValidator $validator,
		RabbitMqPluginPublishers\IRabbitMqPublisher $rabbitMqPublisher,
		Log\LoggerInterface $logger
	) {
		$this->triggerRepository = $triggerRepository;
		$this->triggersManager = $triggersManager;
		$this->actionRepository = $actionRepository;
		$this->actionsManager = $actionsManager;
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

		if ($routingKey === ModulesMetadata\Constants::MESSAGE_BUS_CHANNELS_PROPERTY_DELETED_ENTITY_ROUTING_KEY) {
			$this->clearProperties(
				$message->offsetGet('device'),
				$message->offsetGet('channel'),
				$message->offsetGet('property')
			);

		} elseif ($routingKey === ModulesMetadata\Constants::MESSAGE_BUS_CHANNELS_PROPERTY_UPDATED_ENTITY_ROUTING_KEY) {
			// Only not pending messages will be processed
			if (
				$message->offsetExists('pending')
				&& $message->offsetGet('pending') === false
				&& $message->offsetExists('value')
			) {
				$this->processChannelConditions(
					$message->offsetGet('device'),
					$message->offsetGet('channel'),
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
				case ModulesMetadata\Constants::MESSAGE_BUS_CHANNELS_PROPERTY_DELETED_ENTITY_ROUTING_KEY:
				case ModulesMetadata\Constants::MESSAGE_BUS_CHANNELS_PROPERTY_UPDATED_ENTITY_ROUTING_KEY:
					return $this->schemaLoader->load(ModulesMetadata\Constants::RESOURCES_FOLDER . '/schemas/devices-module/entity.channel.property.json');
			}
		}

		return null;
	}

	/**
	 * @param string $device
	 * @param string $channel
	 * @param string $property
	 *
	 * @return void
	 */
	private function clearProperties(string $device, string $channel, string $property): void
	{
		$findQuery = new TriggersModuleQueries\FindChannelPropertyTriggersQuery();
		$findQuery->forProperty($device, $channel, $property);

		$triggers = $this->triggerRepository->findAllBy($findQuery, TriggersModuleEntities\Triggers\ChannelPropertyTrigger::class);

		foreach ($triggers as $trigger) {
			$this->triggersManager->delete($trigger);
		}

		$findQuery = new TriggersModuleQueries\FindActionsQuery();
		$findQuery->forChannelProperty($device, $channel, $property);

		$actions = $this->actionRepository->findAllBy($findQuery, TriggersModuleEntities\Actions\ChannelPropertyAction::class);

		foreach ($actions as $action) {
			$this->actionsManager->delete($action);
		}

		$findQuery = new TriggersModuleQueries\FindConditionsQuery();
		$findQuery->forChannelProperty($device, $channel, $property);

		$conditions = $this->conditionRepository->findAllBy($findQuery, TriggersModuleEntities\Conditions\ChannelPropertyCondition::class);

		foreach ($conditions as $condition) {
			$this->conditionsManager->delete($condition);
		}

		$this->logger->info('[CONSUMER] Successfully consumed channel property data message');
	}

	/**
	 * @param string $device
	 * @param string $channel
	 * @param string $property
	 * @param mixed $value
	 * @param mixed|null $previousValue
	 * @param string|null $datatype
	 *
	 * @return void
	 */
	private function processChannelConditions(
		string $device,
		string $channel,
		string $property,
		$value,
		$previousValue = null,
		?string $datatype = null
	): void {
		$value = $this->formatValue($value, $datatype);
		$previousValue = $this->formatValue($previousValue, $datatype);

		// Previous value is same as current, skipping
		if ($previousValue !== null && (string) $value === (string) $previousValue) {
			return;
		}

		$findQuery = new TriggersModuleQueries\FindConditionsQuery();
		$findQuery->forChannelProperty($device, $channel, $property);

		$conditions = $this->conditionRepository->findAllBy($findQuery, TriggersModuleEntities\Conditions\ChannelPropertyCondition::class);

		/** @var TriggersModuleEntities\Conditions\ChannelPropertyCondition $condition */
		foreach ($conditions as $condition) {
			if (
				$condition->getOperator()->equalsValue(TriggersModuleTypes\ConditionOperatorType::STATE_VALUE_EQUAL)
				&& $condition->getOperand() === (string) $value
			) {
				$this->processCondition($condition);
			}
		}

		$findQuery = new TriggersModuleQueries\FindChannelPropertyTriggersQuery();
		$findQuery->forProperty($device, $channel, $property);

		$triggers = $this->triggerRepository->findAllBy($findQuery, TriggersModuleEntities\Triggers\ChannelPropertyTrigger::class);

		/** @var TriggersModuleEntities\Triggers\ChannelPropertyTrigger $trigger */
		foreach ($triggers as $trigger) {
			if (
				$trigger->getOperator()->equalsValue(TriggersModuleTypes\ConditionOperatorType::STATE_VALUE_EQUAL)
				&& $trigger->getOperand() === (string) $value
			) {
				$this->processTrigger($trigger);
			}
		}
	}

}
