<?php declare(strict_types = 1);

/**
 * ChannelMessageHandler.php
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
 * Channel command messages consumer
 *
 * @package        FastyBird:TriggersNode!
 * @subpackage     Consumers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class ChannelMessageHandler implements RabbitMqPluginConsumers\IMessageHandler
{

	/** @var TriggersModuleModels\Triggers\ITriggerRepository */
	private TriggersModuleModels\Triggers\ITriggerRepository $triggerRepository;

	/** @var TriggersModuleModels\Triggers\ITriggersManager */
	private TriggersModuleModels\Triggers\ITriggersManager $triggersManager;

	/** @var TriggersModuleModels\Actions\IActionRepository */
	private TriggersModuleModels\Actions\IActionRepository $actionRepository;

	/** @var TriggersModuleModels\Actions\IActionsManager */
	private TriggersModuleModels\Actions\IActionsManager $actionsManager;

	/** @var TriggersModuleModels\Conditions\IConditionRepository */
	private TriggersModuleModels\Conditions\IConditionRepository $conditionRepository;

	/** @var TriggersModuleModels\Conditions\IConditionsManager */
	private TriggersModuleModels\Conditions\IConditionsManager $conditionsManager;

	/** @var ModulesMetadataLoaders\ISchemaLoader */
	private ModulesMetadataLoaders\ISchemaLoader $schemaLoader;

	/** @var ModulesMetadataSchemas\IValidator */
	private ModulesMetadataSchemas\IValidator $validator;

	/** @var Log\LoggerInterface */
	private Log\LoggerInterface $logger;

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
					return $this->schemaLoader->load(ModulesMetadata\Constants::RESOURCES_FOLDER . '/schemas/devices-module/entity.channel.json');
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
		$findQuery = new TriggersModuleQueries\FindChannelPropertyTriggersQuery();
		$findQuery->forChannel($device, $channel);

		$triggers = $this->triggerRepository->findAllBy($findQuery, TriggersModuleEntities\Triggers\ChannelPropertyTrigger::class);

		foreach ($triggers as $trigger) {
			$this->triggersManager->delete($trigger);
		}

		$findQuery = new TriggersModuleQueries\FindActionsQuery();
		$findQuery->forChannel($device, $channel);

		$actions = $this->actionRepository->findAllBy($findQuery, TriggersModuleEntities\Actions\ChannelPropertyAction::class);

		foreach ($actions as $action) {
			$this->actionsManager->delete($action);
		}

		$findQuery = new TriggersModuleQueries\FindConditionsQuery();
		$findQuery->forChannel($device, $channel);

		$conditions = $this->conditionRepository->findAllBy($findQuery, TriggersModuleEntities\Conditions\ChannelPropertyCondition::class);

		foreach ($conditions as $condition) {
			$this->conditionsManager->delete($condition);
		}

		$this->logger->info('[CONSUMER] Successfully consumed channel entity message');
	}

}
