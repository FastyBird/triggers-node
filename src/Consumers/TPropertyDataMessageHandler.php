<?php declare(strict_types = 1);

/**
 * TPropertyDataMessageHandler.php
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

use FastyBird\NodeExchange\Publishers as NodeExchangePublishers;
use FastyBird\TriggersNode;
use FastyBird\TriggersNode\Entities;
use Psr\Log;

/**
 * Device or channel property data command messages trait
 *
 * @package        FastyBird:TriggersNode!
 * @subpackage     Consumers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @property-read NodeExchangePublishers\IRabbitMqPublisher $rabbitMqPublisher
 * @property-read Log\LoggerInterface $logger
 */
trait TPropertyDataMessageHandler
{

	/**
	 * @param Entities\Conditions\ICondition $condition
	 *
	 * @return void
	 */
	protected function processCondition(
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
	 * @param mixed $value
	 * @param string|null $datatype
	 *
	 * @return int|float|string|bool|null
	 */
	protected function formatValue($value, ?string $datatype)
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

}
