<?php declare(strict_types = 1);

namespace Tests\Cases;

use FastyBird\NodeExchange\Publishers as NodeExchangePublishers;
use FastyBird\TriggersNode;
use FastyBird\TriggersNode\Consumers;
use FastyBird\TriggersNode\Entities;
use FastyBird\TriggersNode\Models;
use FastyBird\TriggersNode\Queries;
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

	public function testProcessMessageDeleteTrigger(): void
	{
		$triggersRepository = $this->getContainer()->getByType(Models\Triggers\TriggerRepository::class);

		$findQuery = new Queries\FindChannelPropertyTriggersQuery();
		$findQuery->forProperty('device-one', 'channel-one', 'button');

		$found = $triggersRepository->findAllBy($findQuery, Entities\Triggers\ChannelPropertyTrigger::class);

		Assert::count(1, $found);

		$routingKey = TriggersNode\Constants::RABBIT_MQ_CHANNELS_PROPERTY_DELETED_ENTITY_ROUTING_KEY;
		$message = Utils\ArrayHash::from([
			'device'   => 'device-one',
			'channel'  => 'channel-one',
			'property' => 'button',
			'name'     => 'button',
		]);

		/** @var Consumers\ChannelPropertyMessageHandler $handler */
		$handler = $this->getContainer()->getByType(Consumers\ChannelPropertyMessageHandler::class);

		$handler->process($routingKey, $message);

		$findQuery = new Queries\FindChannelPropertyTriggersQuery();
		$findQuery->forProperty('device-one', 'channel-one', 'button');

		$found = $triggersRepository->findAllBy($findQuery, Entities\Triggers\ChannelPropertyTrigger::class);

		Assert::count(0, $found);
	}

	public function testProcessMessageDeleteAction(): void
	{
		$actionRepository = $this->getContainer()->getByType(Models\Actions\ActionRepository::class);

		$findQuery = new Queries\FindActionsQuery();
		$findQuery->forChannelProperty('device-one', 'channel-four', 'switch');

		$found = $actionRepository->findAllBy($findQuery, Entities\Actions\ChannelPropertyAction::class);

		Assert::count(1, $found);

		$routingKey = TriggersNode\Constants::RABBIT_MQ_CHANNELS_PROPERTY_DELETED_ENTITY_ROUTING_KEY;
		$message = Utils\ArrayHash::from([
			'device'   => 'device-one',
			'channel'  => 'channel-four',
			'property' => 'switch',
			'name'     => 'switch',
		]);

		/** @var Consumers\ChannelPropertyMessageHandler $handler */
		$handler = $this->getContainer()->getByType(Consumers\ChannelPropertyMessageHandler::class);

		$handler->process($routingKey, $message);

		$findQuery = new Queries\FindActionsQuery();
		$findQuery->forChannelProperty('device-one', 'channel-four', 'switch');

		$found = $actionRepository->findAllBy($findQuery, Entities\Actions\ChannelPropertyAction::class);

		Assert::count(0, $found);
	}

	public function testProcessMessageFireAction(): void
	{
		$routingKey = TriggersNode\Constants::RABBIT_MQ_CHANNELS_PROPERTY_UPDATED_ENTITY_ROUTING_KEY;
		$message = Utils\ArrayHash::from([
			'device'   => 'device-one',
			'channel'  => 'channel-one',
			'property' => 'button',
			'value'    => '3',
			'pending'  => false,
			'datatype' => null,
			'format'   => null,
		]);

		$rabbitPublisher = Mockery::mock(NodeExchangePublishers\RabbitMqPublisher::class);
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
			NodeExchangePublishers\IRabbitMqPublisher::class,
			$rabbitPublisher
		);

		$handler = $this->getContainer()->getByType(Consumers\ChannelPropertyMessageHandler::class);

		$handler->process($routingKey, $message);
	}

}

$test_case = new ChannelPropertyMessageHandlerTest();
$test_case->run();
