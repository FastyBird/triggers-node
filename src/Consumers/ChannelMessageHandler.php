<?php declare(strict_types = 1);

/**
 * ChannelMessageHandler.php
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
 * Channel command messages consumer
 *
 * @package        FastyBird:TriggersNode!
 * @subpackage     Consumers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class ChannelMessageHandler implements NodeExchangeConsumers\IMessageHandler
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
		if ($routingKey === TriggersNode\Constants::RABBIT_MQ_CHANNELS_DELETED_ENTITY_ROUTING_KEY) {
			$this->clearChannels(
				$message->offsetGet('device'),
				$message->offsetGet('channel')
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
				case TriggersNode\Constants::RABBIT_MQ_CHANNELS_DELETED_ENTITY_ROUTING_KEY:
					return $this->schemaLoader->load(NodeMetadata\Constants::RESOURCES_FOLDER . '/schemas/devices-node/entity.channel.json');
			}
		}

		return null;
	}

	/**
	 * @param string $device
	 * @param string $channel
	 *
	 * @return void
	 */
	private function clearChannels(string $device, string $channel): void
	{
		$findQuery = new Queries\FindChannelPropertyTriggersQuery();
		$findQuery->forChannel($device, $channel);

		$triggers = $this->triggerRepository->findAllBy($findQuery, Entities\Triggers\ChannelPropertyTrigger::class);

		foreach ($triggers as $trigger) {
			$this->triggersManager->delete($trigger);
		}

		/** @var Queries\FindActionsQuery<Entities\Actions\ChannelPropertyAction> $findQuery */
		$findQuery = new Queries\FindActionsQuery();
		$findQuery->forChannel($device, $channel);

		$actions = $this->actionRepository->findAllBy($findQuery, Entities\Actions\ChannelPropertyAction::class);

		foreach ($actions as $action) {
			$this->actionsManager->delete($action);
		}

		/** @var Queries\FindConditionsQuery<Entities\Conditions\ChannelPropertyCondition> $findQuery */
		$findQuery = new Queries\FindConditionsQuery();
		$findQuery->forChannel($device, $channel);

		$conditions = $this->conditionRepository->findAllBy($findQuery, Entities\Conditions\ChannelPropertyCondition::class);

		foreach ($conditions as $condition) {
			$this->conditionsManager->delete($condition);
		}

		$this->logger->info('[CONSUMER] Successfully consumed channel entity message');
	}

}
