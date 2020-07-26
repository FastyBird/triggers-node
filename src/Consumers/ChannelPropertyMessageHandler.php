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
use FastyBird\NodeMetadata;
use FastyBird\NodeMetadata\Loaders as NodeMetadataLoaders;
use FastyBird\TriggersNode;
use FastyBird\TriggersNode\Entities;
use FastyBird\TriggersNode\Exceptions;
use FastyBird\TriggersNode\Models;
use FastyBird\TriggersNode\Queries;
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

	/** @var Log\LoggerInterface */
	private $logger;

	public function __construct(
		Models\Triggers\ITriggerRepository $triggerRepository,
		Models\Triggers\ITriggersManager $triggersManager,
		Models\Actions\IActionRepository $actionRepository,
		Models\Actions\IActionsManager $actionsManager,
		Models\Conditions\IConditionRepository $conditionRepository,
		Models\Conditions\IConditionsManager $conditionsManager,
		NodeMetadataLoaders\ISchemaLoader $schemaLoader,
		Log\LoggerInterface $logger
	) {
		$this->triggerRepository = $triggerRepository;
		$this->triggersManager = $triggersManager;
		$this->actionRepository = $actionRepository;
		$this->actionsManager = $actionsManager;
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
		if ($routingKey === TriggersNode\Constants::RABBIT_MQ_CHANNELS_PROPERTY_DELETED_ENTITY_ROUTING_KEY) {
			$this->clearProperties(
				$message->offsetGet('device'),
				$message->offsetGet('channel'),
				$message->offsetGet('property')
			);

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

		$this->clearTriggers($findQuery);

		/** @var Queries\FindActionsQuery<Entities\Actions\ChannelPropertyAction> $findQuery */
		$findQuery = new Queries\FindActionsQuery();
		$findQuery->forChannelProperty($device, $channel, $property);

		$this->clearActions($findQuery);

		/** @var Queries\FindConditionsQuery<Entities\Conditions\ChannelPropertyCondition> $findQuery */
		$findQuery = new Queries\FindConditionsQuery();
		$findQuery->forChannelProperty($device, $channel, $property);

		$this->clearConditions($findQuery);

		$this->logger->info('[CONSUMER] Successfully consumed channel property data message');
	}

	/**
	 * @param Queries\FindChannelPropertyTriggersQuery $findQuery
	 *
	 * @return void
	 *
	 * @phpstan-template T of Entities\Triggers\ChannelPropertyTrigger
	 * @phpstan-param    Queries\FindChannelPropertyTriggersQuery<T> $findQuery
	 */
	private function clearTriggers(Queries\FindChannelPropertyTriggersQuery $findQuery): void
	{
		$triggers = $this->triggerRepository->findAllBy($findQuery, Entities\Triggers\ChannelPropertyTrigger::class);

		foreach ($triggers as $trigger) {
			$this->triggersManager->delete($trigger);
		}
	}

	/**
	 * @param Queries\FindActionsQuery $findQuery
	 *
	 * @return void
	 *
	 * @phpstan-template T of Entities\Actions\ChannelPropertyAction
	 * @phpstan-param    Queries\FindActionsQuery<T> $findQuery
	 */
	private function clearActions(Queries\FindActionsQuery $findQuery): void
	{
		$actions = $this->actionRepository->findAllBy($findQuery, Entities\Actions\ChannelPropertyAction::class);

		foreach ($actions as $action) {
			$this->actionsManager->delete($action);
		}
	}

	/**
	 * @param Queries\FindConditionsQuery $findQuery
	 *
	 * @return void
	 *
	 * @phpstan-template T of Entities\Conditions\ChannelPropertyCondition
	 * @phpstan-param    Queries\FindConditionsQuery<T> $findQuery
	 */
	private function clearConditions(Queries\FindConditionsQuery $findQuery): void
	{
		$conditions = $this->conditionRepository->findAllBy($findQuery, Entities\Conditions\ChannelPropertyCondition::class);

		foreach ($conditions as $condition) {
			$this->conditionsManager->delete($condition);
		}
	}

}
