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
final class ChannelPropertyMessageHandlerTest extends DbTestCase
{

	/**
	 * @param string $routingKey
	 * @param Utils\ArrayHash $message
	 * @param int $publishCallCount
	 * @param mixed[] $fixture
	 * @param int $totalTriggersBefore
	 * @param int $totalTriggersAfter
	 *
	 * @dataProvider ./../../../fixtures/Consumers/channelPropertyDeleteMessage.php
	 */
	public function testProcessMessageDeleteTrigger(
		string $routingKey,
		Utils\ArrayHash $message,
		int $publishCallCount,
		array $fixture,
		int $totalTriggersBefore,
		int $totalTriggersAfter
	): void {
		$triggersRepository = $this->getContainer()->getByType(TriggersModuleModels\Triggers\TriggerRepository::class);

		$findQuery = new TriggersModuleQueries\FindChannelPropertyTriggersQuery();
		$findQuery->forProperty('device-one', 'channel-one', 'button');

		$found = $triggersRepository->findAllBy($findQuery, TriggersModuleEntities\Triggers\ChannelPropertyTrigger::class);

		Assert::count($totalTriggersBefore, $found);

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

		/** @var Consumers\ChannelPropertyMessageHandler $handler */
		$handler = $this->getContainer()->getByType(Consumers\ChannelPropertyMessageHandler::class);

		$handler->process($routingKey, TriggersNode\Constants::NODE_DEVICES_ORIGIN, Utils\Json::encode($message));

		$findQuery = new TriggersModuleQueries\FindChannelPropertyTriggersQuery();
		$findQuery->forProperty('device-one', 'channel-one', 'button');

		$found = $triggersRepository->findAllBy($findQuery, TriggersModuleEntities\Triggers\ChannelPropertyTrigger::class);

		Assert::count($totalTriggersAfter, $found);
	}

	/**
	 * @param string $routingKey
	 * @param Utils\ArrayHash $message
	 * @param int $publishCallCount
	 * @param mixed[] $fixture
	 * @param int $totalTriggersBefore
	 * @param int $totalTriggersAfter
	 * @param int $totalActionsBefore
	 * @param int $totalActionsAfter
	 *
	 * @dataProvider ./../../../fixtures/Consumers/channelPropertyDeleteMessage.php
	 */
	public function testProcessMessageDeleteAction(
		string $routingKey,
		Utils\ArrayHash $message,
		int $publishCallCount,
		array $fixture,
		int $totalTriggersBefore,
		int $totalTriggersAfter,
		int $totalActionsBefore,
		int $totalActionsAfter
	): void {
		$actionRepository = $this->getContainer()->getByType(TriggersModuleModels\Actions\ActionRepository::class);

		$findQuery = new TriggersModuleQueries\FindActionsQuery();
		$findQuery->forChannelProperty('device-one', 'channel-four', 'switch');

		$found = $actionRepository->findAllBy($findQuery, TriggersModuleEntities\Actions\ChannelPropertyAction::class);

		Assert::count($totalActionsBefore, $found);

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

		/** @var Consumers\ChannelPropertyMessageHandler $handler */
		$handler = $this->getContainer()->getByType(Consumers\ChannelPropertyMessageHandler::class);

		$handler->process($routingKey, TriggersNode\Constants::NODE_DEVICES_ORIGIN, Utils\Json::encode($message));

		$findQuery = new TriggersModuleQueries\FindActionsQuery();
		$findQuery->forChannelProperty('device-one', 'channel-four', 'switch');

		$found = $actionRepository->findAllBy($findQuery, TriggersModuleEntities\Actions\ChannelPropertyAction::class);

		Assert::count($totalActionsAfter, $found);
	}

	public function testProcessMessageFireAction(): void
	{
		$routingKey = TriggersNode\Constants::RABBIT_MQ_CHANNELS_PROPERTY_UPDATED_ENTITY_ROUTING_KEY;
		$message = Utils\ArrayHash::from([
			'id'       => 'fe2badf6-2e85-4ef6-9009-fe247d473069',
			'device'   => 'device-one',
			'channel'  => 'channel-one',
			'property' => 'button',
			'value'    => '3',
			'pending'  => false,
			'datatype' => null,
			'format'   => null,
			'owner'    => '89ce7161-12dd-427e-9a35-92bc4390d98d',
		]);

		$rabbitPublisher = Mockery::mock(RabbitMqPluginPublishers\RabbitMqPublisher::class);
		$rabbitPublisher
			->shouldReceive('publish')
			->withArgs(function (string $routingKey, array $data): bool {
				Assert::same(TriggersNode\Constants::RABBIT_MQ_CHANNELS_PROPERTIES_DATA_ROUTING_KEY, $routingKey);
				Assert::same(
					[
						'device'   => 'device-two',
						'channel'  => 'channel-one',
						'property' => 'switch',
						'expected' => 'toggle',
					],
					$data
				);

				return true;
			});

		$this->mockContainerService(
			RabbitMqPluginPublishers\IRabbitMqPublisher::class,
			$rabbitPublisher
		);

		$handler = $this->getContainer()->getByType(Consumers\ChannelPropertyMessageHandler::class);

		$handler->process($routingKey, TriggersNode\Constants::NODE_DEVICES_ORIGIN, Utils\Json::encode($message));
	}

}

$test_case = new ChannelPropertyMessageHandlerTest();
$test_case->run();
