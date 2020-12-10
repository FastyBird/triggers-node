<?php declare(strict_types = 1);

namespace Tests\Cases;

use FastyBird\RabbitMqPlugin\Publishers as RabbitMqPluginPublishers;
use FastyBird\TriggersModule\Entities as TriggersModuleEntities;
use FastyBird\TriggersModule\Models as TriggersModuleModels;
use FastyBird\TriggersModule\Queries as TriggersModuleQueries;
use FastyBird\TriggersNode;
use FastyBird\TriggersNode\Consumers;
use Mockery;
use Nette\Utils;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';
require_once __DIR__ . '/../DbTestCase.php';

/**
 * @testCase
 */
final class DeviceMessageHandlerTest extends DbTestCase
{

	/**
	 * @param string $routingKey
	 * @param Utils\ArrayHash $message
	 * @param int $publishCallCount
	 * @param mixed[] $fixture
	 *
	 * @dataProvider ./../../../fixtures/Consumers/deviceDeleteMessage.php
	 */
	public function testProcessMessageDelete(string $routingKey, Utils\ArrayHash $message, int $publishCallCount, array $fixture): void
	{
		$triggersRepository = $this->getContainer()->getByType(TriggersModuleModels\Triggers\TriggerRepository::class);
		$actionRepository = $this->getContainer()->getByType(TriggersModuleModels\Actions\ActionRepository::class);

		$findQuery = new TriggersModuleQueries\FindChannelPropertyTriggersQuery();
		$findQuery->forDevice('device-one');

		$found = $triggersRepository->findAllBy($findQuery, TriggersModuleEntities\Triggers\ChannelPropertyTrigger::class);

		Assert::count(2, $found);

		$findQuery = new TriggersModuleQueries\FindActionsQuery();
		$findQuery->forDevice('device-one');

		$found = $actionRepository->findAllBy($findQuery, TriggersModuleEntities\Actions\ChannelPropertyAction::class);

		Assert::count(6, $found);

		$rabbitPublisher = Mockery::mock(RabbitMqPluginPublishers\RabbitMqPublisher::class);
		$rabbitPublisher
			->shouldReceive('publish')
			->withArgs(function (string $routingKey, array $data) use ($fixture): bool {
				if (Utils\Strings::contains($routingKey, 'created')) {
					unset($data['id']);
				}

				Assert::false($data === []);
				Assert::true(isset($fixture[$routingKey]));

				if (isset($fixture[$routingKey]['primaryKey'])) {
					Assert::equal($fixture[$routingKey][$data[$fixture[$routingKey]['primaryKey']]], $data);

				} else {
					Assert::equal($fixture[$routingKey], $data);
				}

				return true;
			})
			->times($publishCallCount);

		$this->mockContainerService(
			RabbitMqPluginPublishers\IRabbitMqPublisher::class,
			$rabbitPublisher
		);

		$handler = $this->getContainer()->getByType(Consumers\DeviceMessageHandler::class);

		$handler->process($routingKey, TriggersNode\Constants::NODE_DEVICES_ORIGIN, Utils\Json::encode($message));

		$findQuery = new TriggersModuleQueries\FindChannelPropertyTriggersQuery();
		$findQuery->forDevice('device-one');

		$found = $triggersRepository->findAllBy($findQuery, TriggersModuleEntities\Triggers\ChannelPropertyTrigger::class);

		Assert::count(0, $found);

		$findQuery = new TriggersModuleQueries\FindActionsQuery();
		$findQuery->forDevice('device-one');

		$found = $actionRepository->findAllBy($findQuery, TriggersModuleEntities\Actions\ChannelPropertyAction::class);

		Assert::count(0, $found);
	}

}

$test_case = new DeviceMessageHandlerTest();
$test_case->run();
