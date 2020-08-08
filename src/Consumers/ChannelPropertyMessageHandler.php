<?php declare(strict_types = 1);

/**
 * ChannelPropertyMessageHandler.php
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
 * Channel property command messages consumer
 *
 * @package        FastyBird:TriggersNode!
 * @subpackage     Consumers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class ChannelPropertyMessageHandler implements NodeExchangeConsumers\IMessageHandler
{

	use TPropertyDataMessageHandler;

	/** @var NodeExchangePublishers\IRabbitMqPublisher */
	protected $rabbitMqPublisher;

	/** @var Log\LoggerInterface */
	protected $logger;

	/** @var Models\Triggers\ITriggerRepository */
	private $triggerRepository;

	/** @var Models\Triggers\ITriggersManager */
	private $triggersManager;

	/** @var Models\Actions\IActionRepository */
	private $actionRepository;

	/** @var Models\Actions\IActionsManager */
	private $actionsManager;

	/** @var Models\Conditions\IConditionRepository */
	private $conditionRepository;

	/** @var Models\Conditions\IConditionsManager */
	private $conditionsManager;

	/** @var NodeMetadataLoaders\ISchemaLoader */
	private $schemaLoader;

	public function __construct(
		Models\Triggers\ITriggerRepository $triggerRepository,
		Models\Triggers\ITriggersManager $triggersManager,
		Models\Actions\IActionRepository $actionRepository,
		Models\Actions\IActionsManager $actionsManager,
		Models\Conditions\IConditionRepository $conditionRepository,
		Models\Conditions\IConditionsManager $conditionsManager,
		NodeMetadataLoaders\ISchemaLoader $schemaLoader,
		NodeExchangePublishers\IRabbitMqPublisher $rabbitMqPublisher,
		Log\LoggerInterface $logger
	) {
		$this->triggerRepository = $triggerRepository;
		$this->triggersManager = $triggersManager;
		$this->actionRepository = $actionRepository;
		$this->actionsManager = $actionsManager;
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
		if ($routingKey === TriggersNode\Constants::RABBIT_MQ_CHANNELS_PROPERTY_DELETED_ENTITY_ROUTING_KEY) {
			$this->clearProperties(
				$message->offsetGet('device'),
				$message->offsetGet('channel'),
				$message->offsetGet('property')
			);

		} elseif ($routingKey === TriggersNode\Constants::RABBIT_MQ_CHANNELS_PROPERTY_UPDATED_ENTITY_ROUTING_KEY) {
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
		if ($origin === TriggersNode\Constants::NODE_DEVICES_ORIGIN) {
			switch ($routingKey) {
				case TriggersNode\Constants::RABBIT_MQ_CHANNELS_PROPERTY_DELETED_ENTITY_ROUTING_KEY:
				case TriggersNode\Constants::RABBIT_MQ_CHANNELS_PROPERTY_UPDATED_ENTITY_ROUTING_KEY:
					return $this->schemaLoader->load(NodeMetadata\Constants::RESOURCES_FOLDER . '/schemas/devices-node/entity.channel.property.json');
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
		$findQuery = new Queries\FindChannelPropertyTriggersQuery();
		$findQuery->forProperty($device, $channel, $property);

		$triggers = $this->triggerRepository->findAllBy($findQuery, Entities\Triggers\ChannelPropertyTrigger::class);

		foreach ($triggers as $trigger) {
			$this->triggersManager->delete($trigger);
		}

		/** @var Queries\FindActionsQuery<Entities\Actions\ChannelPropertyAction> $findQuery */
		$findQuery = new Queries\FindActionsQuery();
		$findQuery->forChannelProperty($device, $channel, $property);

		$actions = $this->actionRepository->findAllBy($findQuery, Entities\Actions\ChannelPropertyAction::class);

		foreach ($actions as $action) {
			$this->actionsManager->delete($action);
		}

		/** @var Queries\FindConditionsQuery<Entities\Conditions\ChannelPropertyCondition> $findQuery */
		$findQuery = new Queries\FindConditionsQuery();
		$findQuery->forChannelProperty($device, $channel, $property);

		$conditions = $this->conditionRepository->findAllBy($findQuery, Entities\Conditions\ChannelPropertyCondition::class);

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

		$findQuery = new Queries\FindConditionsQuery();
		$findQuery->forChannelProperty($device, $channel, $property);

		$conditions = $this->conditionRepository->findAllBy($findQuery, Entities\Conditions\ChannelPropertyCondition::class);

		/** @var Entities\Conditions\ChannelPropertyCondition $condition */
		foreach ($conditions as $condition) {
			if (
				$condition->getOperator()->equalsValue(Types\ConditionOperatorType::STATE_VALUE_EQUAL)
				&& $condition->getOperand() === (string) $value
			) {
				$this->processCondition($condition);
			}
		}

		$findQuery = new Queries\FindChannelPropertyTriggersQuery();
		$findQuery->forProperty($device, $channel, $property);

		$triggers = $this->triggerRepository->findAllBy($findQuery, Entities\Triggers\ChannelPropertyTrigger::class);

		/** @var Entities\Triggers\ChannelPropertyTrigger $trigger */
		foreach ($triggers as $trigger) {
			if (
				$trigger->getOperator()->equalsValue(Types\ConditionOperatorType::STATE_VALUE_EQUAL)
				&& $trigger->getOperand() === (string) $value
			) {
				$this->processTrigger($trigger);
			}
		}
	}

}
