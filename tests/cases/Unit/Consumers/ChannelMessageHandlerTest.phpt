<?php declare(strict_types = 1);

namespace Tests\Cases;

use FastyBird\TriggersNode;
use FastyBird\TriggersNode\Consumers;
use FastyBird\TriggersNode\Entities;
use FastyBird\TriggersNode\Models;
use FastyBird\TriggersNode\Queries;
use Nette\Utils;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';
require_once __DIR__ . '/../DbTestCase.php';

/**
 * @testCase
 */
final class ChannelMessageHandlerTest extends DbTestCase
{

	public function testProcessMessageDelete(): void
	{
		$triggersRepository = $this->getContainer()->getByType(Models\Triggers\TriggerRepository::class);
		$actionRepository = $this->getContainer()->getByType(Models\Actions\ActionRepository::class);

		$findQuery = new Queries\FindChannelPropertyTriggersQuery();
		$findQuery->forChannel('device-one', 'channel-one');

		$found = $triggersRepository->findAllBy($findQuery, Entities\Triggers\ChannelPropertyTrigger::class);

		Assert::count(1, $found);

		$findQuery = new Queries\FindActionsQuery();
		$findQuery->forChannel('device-one', 'channel-one');

		$found = $actionRepository->findAllBy($findQuery, Entities\Actions\ChannelPropertyAction::class);

		Assert::count(1, $found);

		$routingKey = TriggersNode\Constants::RABBIT_MQ_CHANNELS_DELETED_ENTITY_ROUTING_KEY;
		$message = Utils\ArrayHash::from([
			'device'  => 'device-one',
			'channel' => 'channel-one',
			'name'    => 'Channel one',
		]);

		$handler = $this->getContainer()->getByType(Consumers\ChannelMessageHandler::class);

		$handler->process($routingKey, $message);

		$findQuery = new Queries\FindChannelPropertyTriggersQuery();
		$findQuery->forChannel('device-one', 'channel-one');

		$found = $triggersRepository->findAllBy($findQuery, Entities\Triggers\ChannelPropertyTrigger::class);

		Assert::count(0, $found);

		$findQuery = new Queries\FindActionsQuery();
		$findQuery->forChannel('device-one', 'channel-one');

		$found = $actionRepository->findAllBy($findQuery, Entities\Actions\ChannelPropertyAction::class);

		Assert::count(0, $found);
	}

}

$test_case = new ChannelMessageHandlerTest();
$test_case->run();
