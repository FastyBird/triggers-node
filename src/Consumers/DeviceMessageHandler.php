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

use FastyBird\ModulesMetadata;
use FastyBird\ModulesMetadata\Loaders as ModulesMetadataLoaders;
use FastyBird\ModulesMetadata\Schemas as ModulesMetadataSchemas;
use FastyBird\RabbitMqPlugin\Consumers as RabbitMqPluginConsumers;
use FastyBird\TriggersModule\Entities as TriggersModuleEntities;
use FastyBird\TriggersModule\Models as TriggersModuleModels;
use FastyBird\TriggersModule\Queries as TriggersModuleQueries;
use FastyBird\TriggersNode;
use FastyBird\TriggersNode\Exceptions;
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
final class DeviceMessageHandler implements RabbitMqPluginConsumers\IMessageHandler
{

	/** @var TriggersModuleModels\Triggers\ITriggerRepository */
	private $triggerRepository;

	/** @var TriggersModuleModels\Triggers\ITriggersManager */
	private $triggersManager;

	/** @var TriggersModuleModels\Actions\IActionRepository */
	private $actionRepository;

	/** @var TriggersModuleModels\Actions\IActionsManager */
	private $actionsManager;

	/** @var TriggersModuleModels\Conditions\IConditionRepository */
	private $conditionRepository;

	/** @var TriggersModuleModels\Conditions\IConditionsManager */
	private $conditionsManager;

	/** @var ModulesMetadataLoaders\ISchemaLoader */
	private $schemaLoader;

	/** @var ModulesMetadataSchemas\IValidator */
	private $validator;

	/** @var Log\LoggerInterface */
	private $logger;

	public function __construct(
		TriggersModuleModels\Triggers\ITriggerRepository $triggerRepository,
		TriggersModuleModels\Triggers\ITriggersManager $triggersManager,
		TriggersModuleModels\Actions\IActionRepository $actionRepository,
		TriggersModuleModels\Actions\IActionsManager $actionsManager,
		TriggersModuleModels\Conditions\IConditionRepository $conditionRepository,
		TriggersModuleModels\Conditions\IConditionsManager $conditionsManager,
		ModulesMetadataLoaders\ISchemaLoader $schemaLoader,
		ModulesMetadataSchemas\IValidator $validator,
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
	public function getSchema(string $routingKey, string $origin): ?string
	{
		if ($origin === TriggersNode\Constants::NODE_DEVICES_ORIGIN) {
			switch ($routingKey) {
				case TriggersNode\Constants::RABBIT_MQ_DEVICES_DELETED_ENTITY_ROUTING_KEY:
					return $this->schemaLoader->load(ModulesMetadata\Constants::RESOURCES_FOLDER . '/schemas/devices-module/entity.device.json');
			}
		}

		return null;
	}

	/**
	 * @param string $device
	 *
	 * @return void
	 */
	private function clearDevices(string $device): void
	{
		$findQuery = new TriggersModuleQueries\FindChannelPropertyTriggersQuery();
		$findQuery->forDevice($device);

		$triggers = $this->triggerRepository->findAllBy($findQuery, TriggersModuleEntities\Triggers\ChannelPropertyTrigger::class);

		foreach ($triggers as $trigger) {
			$this->triggersManager->delete($trigger);
		}

		$findQuery = new TriggersModuleQueries\FindActionsQuery();
		$findQuery->forDevice($device);

		$actions = $this->actionRepository->findAllBy($findQuery, TriggersModuleEntities\Actions\ChannelPropertyAction::class);

		foreach ($actions as $action) {
			$this->actionsManager->delete($action);
		}

		$findQuery = new TriggersModuleQueries\FindConditionsQuery();
		$findQuery->forDevice($device);

		$conditions = $this->conditionRepository->findAllBy($findQuery, TriggersModuleEntities\Conditions\DevicePropertyCondition::class);

		foreach ($conditions as $condition) {
			$this->conditionsManager->delete($condition);
		}

		$findQuery = new TriggersModuleQueries\FindConditionsQuery();
		$findQuery->forDevice($device);

		$conditions = $this->conditionRepository->findAllBy($findQuery, TriggersModuleEntities\Conditions\ChannelPropertyCondition::class);

		foreach ($conditions as $condition) {
			$this->conditionsManager->delete($condition);
		}

		$this->logger->info('[CONSUMER] Successfully consumed device entity message');
	}

}
