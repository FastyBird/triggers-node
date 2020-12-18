<?php declare(strict_types = 1);

/**
 * TPropertyDataMessageHandler.php
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

use FastyBird\ModulesMetadata;
use FastyBird\RabbitMqPlugin\Publishers as RabbitMqPluginPublishers;
use FastyBird\TriggersModule;
use FastyBird\TriggersModule\Entities as TriggersModuleEntities;
use Psr\Log;

/**
 * Device or channel property data command messages trait
 *
 * @package        FastyBird:TriggersNode!
 * @subpackage     Consumers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @property-read RabbitMqPluginPublishers\IRabbitMqPublisher $rabbitMqPublisher
 * @property-read Log\LoggerInterface $logger
 */
trait TPropertyDataMessageHandler
{

	/**
	 * @param TriggersModuleEntities\Conditions\ICondition $condition
	 *
	 * @return void
	 */
	protected function processCondition(
		TriggersModuleEntities\Conditions\ICondition $condition
	): void {
		$trigger = $condition->getTrigger();

		if (count($trigger->getActions()) === 0) {
			return;
		}

		foreach ($trigger->getConditions() as $triggerCondition) {
			if (!$triggerCondition->getId()->equals($condition->getId())) {
				// Check if all conditions are passed

				if ($triggerCondition instanceof TriggersModuleEntities\Conditions\IChannelPropertyCondition) {
					$value = $this->fetchChannelPropertyValue(
						$triggerCondition->getDevice(),
						$triggerCondition->getChannel(),
						$triggerCondition->getProperty()
					);

					if ($value !== $triggerCondition->getOperand()) {
						$this->logger->info('[CONSUMER] Trigger do not met all conditions, skipping');

						return;
					}

				} elseif ($triggerCondition instanceof TriggersModuleEntities\Conditions\IDevicePropertyCondition) {
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
	 * @param TriggersModuleEntities\Triggers\ITrigger $trigger
	 *
	 * @return void
	 */
	private function processTrigger(
		TriggersModuleEntities\Triggers\ITrigger $trigger
	): void {
		foreach ($trigger->getActions() as $action) {
			if ($action instanceof TriggersModuleEntities\Actions\ChannelPropertyAction) {
				$this->rabbitMqPublisher->publish(
					ModulesMetadata\Constants::MESSAGE_BUS_CHANNELS_PROPERTIES_DATA_ROUTING_KEY,
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
			case TriggersModule\Constants::DATA_TYPE_INTEGER:
				return (int) $value;

			case TriggersModule\Constants::DATA_TYPE_FLOAT:
				return (float) $value;

			case TriggersModule\Constants::DATA_TYPE_BOOLEAN:
				return (bool) $value;

			case TriggersModule\Constants::DATA_TYPE_STRING:
			case TriggersModule\Constants::DATA_TYPE_ENUM:
			case TriggersModule\Constants::DATA_TYPE_COLOR:
			default:
				return $value;
		}
	}

}
