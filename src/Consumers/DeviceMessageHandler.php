<?php declare(strict_types = 1);

/**
 * DeviceMessageHandler.php
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
use FastyBird\NodeLibs\Exceptions as NodeLibsExceptions;
use FastyBird\NodeLibs\Helpers as NodeLibsHelpers;
use FastyBird\TriggersNode;
use FastyBird\TriggersNode\Entities;
use FastyBird\TriggersNode\Exceptions;
use FastyBird\TriggersNode\Models;
use FastyBird\TriggersNode\Queries;
use Nette\Utils;
use Psr\Log;
use Throwable;

/**
 * Device command messages consumer
 *
 * @package        FastyBird:TriggersNode!
 * @subpackage     Consumers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class DeviceMessageHandler implements NodeLibsConsumers\IMessageHandler
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

	/** @var NodeLibsHelpers\ISchemaLoader */
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
		NodeLibsHelpers\ISchemaLoader $schemaLoader,
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
	 *
	 * @throws NodeLibsExceptions\TerminateException
	 */
	public function process(
		string $routingKey,
		Utils\ArrayHash $message
	): bool {
		if ($routingKey === TriggersNode\Constants::RABBIT_MQ_DEVICES_DELETED_ENTITY_ROUTING_KEY) {
			$this->clearDevices(
				$message->offsetGet('device')
			);

		} else {
			throw new Exceptions\InvalidStateException('Unknown routing key');
		}

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getAllowedOrigin(string $routingKey)
	{
		return TriggersNode\Constants::NODE_DEVICES_ORIGIN;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getSchema(string $routingKey): string
	{
		switch ($routingKey) {
			case TriggersNode\Constants::RABBIT_MQ_DEVICES_DELETED_ENTITY_ROUTING_KEY:
				return $this->schemaLoader->load('entity.device.json');

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
			TriggersNode\Constants::RABBIT_MQ_DEVICES_DELETED_ENTITY_ROUTING_KEY,
		];
	}

	/**
	 * @param string $device
	 *
	 * @return void
	 *
	 * @throws NodeLibsExceptions\TerminateException
	 */
	private function clearDevices(string $device): void
	{
		try {
			$findQuery = new Queries\FindChannelPropertyTriggersQuery();
			$findQuery->forDevice($device);

			$this->clearTriggers($findQuery);

			/** @var Queries\FindActionsQuery<Entities\Actions\ChannelPropertyAction> $findQuery */
			$findQuery = new Queries\FindActionsQuery();
			$findQuery->forDevice($device);

			$this->clearActions($findQuery);

			/** @var Queries\FindConditionsQuery<Entities\Conditions\DevicePropertyCondition> $findQuery */
			$findQuery = new Queries\FindConditionsQuery();
			$findQuery->forDevice($device);

			$conditions = $this->conditionRepository->findAllBy($findQuery, Entities\Conditions\DevicePropertyCondition::class);

			foreach ($conditions as $condition) {
				$this->conditionsManager->delete($condition);
			}

			/** @var Queries\FindConditionsQuery<Entities\Conditions\ChannelPropertyCondition> $findQuery */
			$findQuery = new Queries\FindConditionsQuery();
			$findQuery->forDevice($device);

			$conditions = $this->conditionRepository->findAllBy($findQuery, Entities\Conditions\ChannelPropertyCondition::class);

			foreach ($conditions as $condition) {
				$this->conditionsManager->delete($condition);
			}

		} catch (Throwable $ex) {
			throw new NodeLibsExceptions\TerminateException('An error occurred: ' . $ex->getMessage(), $ex->getCode(), $ex);
		}

		$this->logger->info('[CONSUMER] Successfully consumed device entity message');
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

}
