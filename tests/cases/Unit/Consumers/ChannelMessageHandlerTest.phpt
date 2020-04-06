<?php declare(strict_types = 1);

namespace Tests\Cases;

use FastyBird\TriggersNode;
use FastyBird\TriggersNode\Consumers;
use FastyBird\TriggersNode\Entities;
use FastyBird\TriggersNode\Models;
use FastyBird\TriggersNode\Queries;
use Nette\Utils;
use Ramsey\Uuid;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';
require_once __DIR__ . '/../DbTestCase.php';

final class ChannelMessageHandlerTest extends DbTestCase
{

	public function testRoutingKeys(): void
	{
		$handler = $this->getContainer()->getByType(Consumers\ChannelMessageHandler::class);

		Assert::same(TriggersNode\Constants::RABBIT_MQ_CHANNELS_ENTITIES_ROUTING_KEY, $handler->getRoutingKeys(true));

		Assert::same([
			TriggersNode\Constants::RABBIT_MQ_CHANNELS_DELETED_ENTITY_ROUTING_KEY,
		], $handler->getRoutingKeys());
	}

	public function testProcessMessageDeleteTrigger(): void
	{
		$triggersRepository = $this->getContainer()->getByType(Models\Triggers\TriggerRepository::class);

		$findQuery = new Queries\FindChannelPropertyTriggersQuery();
		$findQuery->byId(Uuid\Uuid::fromString('1c580923-28dd-4b28-8517-bf37f0173b93'));

		$found = $triggersRepository->findOneBy($findQuery, Entities\Triggers\ChannelPropertyTrigger::class);

		Assert::true($found !== null);

		$routingKey = TriggersNode\Constants::RABBIT_MQ_CHANNELS_DELETED_ENTITY_ROUTING_KEY;
		$message = Utils\ArrayHash::from([
			'device'  => 'device-one',
			'channel' => 'channel-one',
			'name'    => 'Channel one',
		]);

		$handler = $this->getContainer()->getByType(Consumers\ChannelMessageHandler::class);

		$handler->process($routingKey, $message);

		$found = $triggersRepository->findOneBy($findQuery, Entities\Triggers\ChannelPropertyTrigger::class);

		Assert::true($found === null);
	}

	public function testProcessMessageDeleteAction(): void
	{
		$actionRepository = $this->getContainer()->getByType(Models\Actions\ActionRepository::class);

		$findQuery = new Queries\FindActionsQuery();
		$findQuery->byId(Uuid\Uuid::fromString('4aa84028-d8b7-4128-95b2-295763634aa4'));

		$found = $actionRepository->findOneBy($findQuery, Entities\Actions\ChannelPropertyAction::class);

		Assert::true($found !== null);

		$routingKey = TriggersNode\Constants::RABBIT_MQ_CHANNELS_DELETED_ENTITY_ROUTING_KEY;
		$message = Utils\ArrayHash::from([
			'device'  => 'device-one',
			'channel' => 'channel-four',
			'name'    => 'Channel one',
		]);

		$handler = $this->getContainer()->getByType(Consumers\ChannelMessageHandler::class);

		$handler->process($routingKey, $message);

		$found = $actionRepository->findOneBy($findQuery, Entities\Actions\ChannelPropertyAction::class);

		Assert::true($found === null);
	}

}

$test_case = new ChannelMessageHandlerTest();
$test_case->run();
