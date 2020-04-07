<?php declare(strict_types = 1);

/**
 * PropertyDataMessageHandler.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:TriggersNode!
 * @subpackage     Consumers
 * @since          0.1.0
 *
 * @date           07.04.20
 */

namespace FastyBird\TriggersNode\Consumers;

use FastyBird\NodeLibs\Consumers as NodeLibsConsumers;
use FastyBird\NodeLibs\Helpers as NodeLibsHelpers;
use FastyBird\NodeLibs\Publishers as NodeLibsPublishers;
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
final class PropertyDataMessageHandler implements NodeLibsConsumers\IMessageHandler
{

	/** @var Models\Triggers\ITriggerRepository */
	private $triggerRepository;

	/** @var Models\Conditions\IConditionRepository */
	private $conditionRepository;

	/** @var NodeLibsHelpers\ISchemaLoader */
	private $schemaLoader;

	/** @var NodeLibsPublishers\IRabbitMqPublisher */
	private $rabbitMqPublisher;

	/** @var Log\LoggerInterface */
	private $logger;

	public function __construct(
		Models\Triggers\ITriggerRepository $triggerRepository,
		Models\Conditions\IConditionRepository $conditionRepository,
		NodeLibsHelpers\ISchemaLoader $schemaLoader,
		NodeLibsPublishers\IRabbitMqPublisher $rabbitMqPublisher,
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
		if ($routingKey === TriggersNode\Constants::RABBIT_MQ_DEVICES_PROPERTIES_DATA_ROUTING_KEY) {
			// Only not pending messages will be processed
			if (!$message->offsetGet('pending')) {
				$this->processDeviceConditions(
					$message->offsetGet('device'),
					$message->offsetGet('property'),
					$message->offsetGet('value'),
					$message->offsetExists('previous_value') ? $message->offsetGet('previous_value') : null,
					$message->offsetGet('datatype'),
					$message->offsetGet('format')
				);
			}

		} elseif ($routingKey === TriggersNode\Constants::RABBIT_MQ_DEVICES_CHANNELS_PROPERTIES_DATA_ROUTING_KEY) {
			// Only not pending messages will be processed
			if (!$message->offsetGet('pending')) {
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
	public function getSchema(string $routingKey): string
	{
		switch ($routingKey) {
			case TriggersNode\Constants::RABBIT_MQ_DEVICES_PROPERTIES_DATA_ROUTING_KEY:
				return $this->schemaLoader->load('data.device.property.json');

			case TriggersNode\Constants::RABBIT_MQ_DEVICES_CHANNELS_PROPERTIES_DATA_ROUTING_KEY:
				return $this->schemaLoader->load('data.channel.property.json');

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
			return TriggersNode\Constants::RABBIT_MQ_DATA_ROUTING_KEY;
		}

		return [
			TriggersNode\Constants::RABBIT_MQ_DEVICES_PROPERTIES_DATA_ROUTING_KEY,

			TriggersNode\Constants::RABBIT_MQ_DEVICES_CHANNELS_PROPERTIES_DATA_ROUTING_KEY,
		];
	}

	/**
	 * @param string $device
	 * @param string $property
	 * @param mixed $value
	 * @param mixed|null $previousValue
	 * @param string|null $datatype
	 * @param string|null $format
	 *
	 * @return void
	 */
	private function processDeviceConditions(
		string $device,
		string $property,
		$value,
		$previousValue = null,
		?string $datatype = null,
		?string $format = null
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
	 * @param string|null $format
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
		?string $format = null
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
				&& $condition->getOperand() === $value
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
				&& $trigger->getOperand() === $value
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

		if (!count($trigger->getActions())) {
			return;
		}

		$allMet = true;

		foreach ($trigger->getConditions() as $triggerCondition) {
			if (!$triggerCondition->getId()->equals($condition->getId())) {
				// Check if all conditions are passed

				if ($triggerCondition instanceof Entities\Conditions\IChannelPropertyCondition) {
					$value = $this->fetchChannelPropertyValue(
						$triggerCondition->getDevice(),
						$triggerCondition->getChannel(),
						$triggerCondition->getProperty()
					);

					if ((string) $value !== $triggerCondition->getOperand()) {
						$this->logger->info('[CONSUMER] Trigger do not met all conditions, skipping');

						$allMet = false;

						continue 1;
					}

				} elseif ($triggerCondition instanceof Entities\Conditions\IDevicePropertyCondition) {
					$value = $this->fetchDevicePropertyValue(
						$triggerCondition->getDevice(),
						$triggerCondition->getProperty()
					);

					if ((string) $value !== $triggerCondition->getOperand()) {
						$this->logger->info('[CONSUMER] Trigger do not met all conditions, skipping');

						$allMet = false;

						continue 1;
					}
				}
			}
		}

		if ($allMet) {
			$this->processTrigger($trigger);
		}
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
					TriggersNode\Constants::RABBIT_MQ_DATA_CHANNEL_PROPERTY_ROUTING_KEY,
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
	 * @return string
	 */
	private function fetchDevicePropertyValue(
		string $device,
		string $property
	): string {
		// TODO: fetch stored value from store
		return '10';
	}

	/**
	 * @param string $device
	 * @param string $channel
	 * @param string $property
	 *
	 * @return string
	 */
	private function fetchChannelPropertyValue(
		string $device,
		string $channel,
		string $property
	): string {
		// TODO: fetch stored value from store
		return '10';
	}

	/**
	 * @param mixed $value
	 * @param string|null $datatype
	 * @param string|null $format
	 *
	 * @return int|float|string|null
	 */
	private function formatValue($value, ?string $datatype, ?string $format)
	{
		return $value;
	}

}
