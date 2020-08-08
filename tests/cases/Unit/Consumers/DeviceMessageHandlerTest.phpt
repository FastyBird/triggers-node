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
final class DeviceMessageHandlerTest extends DbTestCase
{

	public function testProcessMessageDelete(): void
	{
		$triggersRepository = $this->getContainer()->getByType(Models\Triggers\TriggerRepository::class);
		$actionRepository = $this->getContainer()->getByType(Models\Actions\ActionRepository::class);

		$findQuery = new Queries\FindChannelPropertyTriggersQuery();
		$findQuery->forDevice('device-one');

		$found = $triggersRepository->findAllBy($findQuery, Entities\Triggers\ChannelPropertyTrigger::class);

		Assert::count(2, $found);

		$findQuery = new Queries\FindActionsQuery();
		$findQuery->forDevice('device-one');

		$found = $actionRepository->findAllBy($findQuery, Entities\Actions\ChannelPropertyAction::class);

		Assert::count(6, $found);

		$routingKey = TriggersNode\Constants::RABBIT_MQ_DEVICES_DELETED_ENTITY_ROUTING_KEY;
		$message = Utils\ArrayHash::from([
			'device' => 'device-one',
			'name'   => 'Device one',
		]);

		$handler = $this->getContainer()->getByType(Consumers\DeviceMessageHandler::class);

		$handler->process($routingKey, $message);

		$findQuery = new Queries\FindChannelPropertyTriggersQuery();
		$findQuery->forDevice('device-one');

		$found = $triggersRepository->findAllBy($findQuery, Entities\Triggers\ChannelPropertyTrigger::class);

		Assert::count(0, $found);

		$findQuery = new Queries\FindActionsQuery();
		$findQuery->forDevice('device-one');

		$found = $actionRepository->findAllBy($findQuery, Entities\Actions\ChannelPropertyAction::class);

		Assert::count(0, $found);
	}

}

$test_case = new DeviceMessageHandlerTest();
$test_case->run();
