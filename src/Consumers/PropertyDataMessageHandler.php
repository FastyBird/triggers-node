<?php declare(strict_types = 1);

/**
 * PropertyDataMessageHandler.php
 *
 * @license        More in license.md
 * @copyright      https://fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:TriggersNode!
 * @subpackage     Consumers
 * @since          0.1.0
 *
 * @date           07.04.20
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
 * Device or channel property data command messages consumer
 *
 * @package        FastyBird:TriggersNode!
 * @subpackage     Consumers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class PropertyDataMessageHandler implements NodeExchangeConsumers\IMessageHandler
{

	/** @var Models\Triggers\ITriggerRepository */
	private $triggerRepository;

	/** @var Models\Conditions\IConditionRepository */
	private $conditionRepository;

	/** @var NodeMetadataLoaders\ISchemaLoader */
	private $schemaLoader;

	/** @var NodeExchangePublishers\IRabbitMqPublisher */
	private $rabbitMqPublisher;

	/** @var Log\LoggerInterface */
	private $logger;

	public function __construct(
		Models\Triggers\ITriggerRepository $triggerRepository,
		Models\Conditions\IConditionRepository $conditionRepository,
		NodeMetadataLoaders\ISchemaLoader $schemaLoader,
		NodeExchangePublishers\IRabbitMqPublisher $rabbitMqPublisher,
		Log\LoggerInterface $logger
	) {
		$this->triggerRepository = $triggerRepository;
		$this->conditionRepository = $conditionRepository;

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
		if (
			$routingKey === TriggersNode\Constants::RABBIT_MQ_DEVICES_PROPERTY_CREATED_ENTITY_ROUTING_KEY
			|| $routingKey === TriggersNode\Constants::RABBIT_MQ_DEVICES_PROPERTY_UPDATED_ENTITY_ROUTING_KEY
		) {
			// Only not pending messages will be processed
			if ($message->offsetGet('pending') === false) {
				$this->processDeviceConditions(
					$message->offsetGet('device'),
					$message->offsetGet('property'),
					$message->offsetGet('value'),
					$message->offsetExists('previous_value') ? $message->offsetGet('previous_value') : null,
					$message->offsetGet('datatype'),
					$message->offsetGet('format')
				);
			}

		} elseif (
			$routingKey === TriggersNode\Constants::RABBIT_MQ_CHANNELS_PROPERTY_CREATED_ENTITY_ROUTING_KEY
			|| $routingKey === TriggersNode\Constants::RABBIT_MQ_CHANNELS_PROPERTY_UPDATED_ENTITY_ROUTING_KEY
		) {
			// Only not pending messages will be processed
			if ($message->offsetGet('pending') === false) {
				$this->processChannelConditions(
					$message->offsetGet('device'),
					$message->offsetGet('channel'),
					$message->offsetGet('property'),
					$message->offsetGet('value'),
					$message->offsetExists('previous_value') ? $message->offsetGet('previous_value') : null,
					$message->offsetGet('datatype'),
					$message->offsetGet('format')
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
		if ($origin === TriggersNode\Constants::NODE_STORAGE_ORIGIN) {
			switch ($routingKey) {
				case TriggersNode\Constants::RABBIT_MQ_DEVICES_PROPERTY_CREATED_ENTITY_ROUTING_KEY:
				case TriggersNode\Constants::RABBIT_MQ_DEVICES_PROPERTY_UPDATED_ENTITY_ROUTING_KEY:
					return $this->schemaLoader->load(NodeMetadata\Constants::RESOURCES_FOLDER . '/schemas/storage-node/entity.device.property.json');

				case TriggersNode\Constants::RABBIT_MQ_CHANNELS_PROPERTY_CREATED_ENTITY_ROUTING_KEY:
				case TriggersNode\Constants::RABBIT_MQ_CHANNELS_PROPERTY_UPDATED_ENTITY_ROUTING_KEY:
					return $this->schemaLoader->load(NodeMetadata\Constants::RESOURCES_FOLDER . '/schemas/storage-node/entity.channel.property.json');
			}
		}

		return null;
	}

	/**
	 * @param string $device
	 * @param string $property
	 * @param mixed $value
	 * @param mixed|null $previousValue
	 * @param string|null $datatype
	 * @param string|string[]|int[]|float[]|null $format
	 *
	 * @return void
	 */
	private function processDeviceConditions(
		string $device,
		string $property,
		$value,
		$previousValue = null,
		?string $datatype = null,
		$format = null
	): void {
		$value = $this->formatValue($value, $datatype, $format);
		$previousValue = $this->formatValue($previousValue, $datatype, $format);

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

	/**
	 * @param string $device
	 * @param string $channel
	 * @param string $property
	 * @param mixed $value
	 * @param mixed|null $previousValue
	 * @param string|null $datatype
	 * @param string|string[]|int[]|float[]|null $format
	 *
	 * @return void
	 */
	private function processChannelConditions(
		string $device,
		string $channel,
		string $property,
		$value,
		$previousValue = null,
		?string $datatype = null,
		$format = null
	): void {
		$value = $this->formatValue($value, $datatype, $format);
		$previousValue = $this->formatValue($previousValue, $datatype, $format);

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

	/**
	 * @param Entities\Conditions\ICondition $condition
	 *
	 * @return void
	 */
	private function processCondition(
		Entities\Conditions\ICondition $condition
	): void {
		$trigger = $condition->getTrigger();

		if (count($trigger->getActions()) === 0) {
			return;
		}

		foreach ($trigger->getConditions() as $triggerCondition) {
			if (!$triggerCondition->getId()->equals($condition->getId())) {
				// Check if all conditions are passed

				if ($triggerCondition instanceof Entities\Conditions\IChannelPropertyCondition) {
					$value = $this->fetchChannelPropertyValue(
						$triggerCondition->getDevice(),
						$triggerCondition->getChannel(),
						$triggerCondition->getProperty()
					);

					if ($value !== $triggerCondition->getOperand()) {
						$this->logger->info('[CONSUMER] Trigger do not met all conditions, skipping');

						return;
					}

				} elseif ($triggerCondition instanceof Entities\Conditions\IDevicePropertyCondition) {
					$value = $this->fetchDevicePropertyValue(
						$triggerCondition->getDevice(),
						$triggerCondition->getProperty()
					);

					if ($value !== $triggerCondition->getOperand()) {
						$this->logger->info('[CONSUMER] Trigger do not met all conditions, skipping');

						return;
					}
				}
			}
		}

		$this->processTrigger($trigger);
	}

	/**
	 * @param Entities\Triggers\ITrigger $trigger
	 *
	 * @return void
	 */
	private function processTrigger(
		Entities\Triggers\ITrigger $trigger
	): void {
		foreach ($trigger->getActions() as $action) {
			if ($action instanceof Entities\Actions\ChannelPropertyAction) {
				$this->rabbitMqPublisher->publish(
					TriggersNode\Constants::RABBIT_MQ_CHANNELS_PROPERTIES_DATA_ROUTING_KEY,
					[
						'device'   => $action->getDevice(),
						'channel'  => $action->getChannel(),
						'property' => $action->getProperty(),
						'expected' => $action->getValue(),
					]
				);

				$this->logger->info('[CONSUMER] Trigger fired command');
			}
		}
	}

	/**
	 * @param string $device
	 * @param string $property
	 *
	 * @return mixed|null
	 */
	private function fetchDevicePropertyValue(
		string $device,
		string $property
	) {
		// TODO: fetch stored value from store
		return null;
	}

	/**
	 * @param string $device
	 * @param string $channel
	 * @param string $property
	 *
	 * @return mixed|null
	 */
	private function fetchChannelPropertyValue(
		string $device,
		string $channel,
		string $property
	) {
		// TODO: fetch stored value from store
		return null;
	}

	/**
	 * @param mixed $value
	 * @param string|null $datatype
	 * @param string|string[]|int[]|float[]|null $format
	 *
	 * @return int|float|string|bool|null
	 */
	private function formatValue($value, ?string $datatype, $format)
	{
		switch ($datatype) {
			case TriggersNode\Constants::DATA_TYPE_INTEGER:
				return (int) $value;

			case TriggersNode\Constants::DATA_TYPE_FLOAT:
				return (float) $value;

			case TriggersNode\Constants::DATA_TYPE_BOOLEAN:
				return (bool) $value;

			case TriggersNode\Constants::DATA_TYPE_STRING:
			case TriggersNode\Constants::DATA_TYPE_ENUM:
			case TriggersNode\Constants::DATA_TYPE_COLOR:
			default:
				return $value;
		}
	}

}
