<?php declare(strict_types = 1);

namespace Tests\Cases;

use FastyBird\NodeLibs\Publishers as NodeLibsPublishers;
use FastyBird\TriggersNode;
use FastyBird\TriggersNode\Consumers;
use Mockery;
use Nette\Utils;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';
require_once __DIR__ . '/../DbTestCase.php';

final class PropertyDataMessageHandlerTest extends DbTestCase
{

	public function testRoutingKeys(): void
	{
		$handler = $this->getContainer()->getByType(Consumers\PropertyDataMessageHandler::class);

		Assert::same(TriggersNode\Constants::RABBIT_MQ_PHYSICALS_ENTITIES_ROUTING_KEY, $handler->getRoutingKeys(true));

		Assert::same([
			TriggersNode\Constants::RABBIT_MQ_ENTITY_PHYSICAL_DEVICE_PROPERTY_CREATED_ROUTING_KEY,
			TriggersNode\Constants::RABBIT_MQ_ENTITY_PHYSICAL_DEVICE_PROPERTY_UPDATED_ROUTING_KEY,
			TriggersNode\Constants::RABBIT_MQ_ENTITY_PHYSICAL_CHANNEL_PROPERTY_CREATED_ROUTING_KEY,
			TriggersNode\Constants::RABBIT_MQ_ENTITY_PHYSICAL_CHANNEL_PROPERTY_UPDATED_ROUTING_KEY,
		], $handler->getRoutingKeys());
	}

	public function testProcessMessageFireAction(): void
	{
		$routingKey = TriggersNode\Constants::RABBIT_MQ_ENTITY_PHYSICAL_CHANNEL_PROPERTY_UPDATED_ROUTING_KEY;

		$message = Utils\ArrayHash::from([
			'device'   => 'device-one',
			'channel'  => 'channel-one',
			'property' => 'button',
			'value'    => '3',
			'pending'  => false,
			'datatype' => null,
			'format'   => null,
		]);

		$rabbitPublisher = Mockery::mock(NodeLibsPublishers\RabbitMqPublisher::class);
		$rabbitPublisher
			->shouldReceive('publish')
			->withArgs(function (string $routingKey, array $data): bool {
				Assert::same(TriggersNode\Constants::RABBIT_MQ_ENTITY_PHYSICAL_CHANNEL_PROPERTY_UPDATED_ROUTING_KEY, $routingKey);
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
			NodeLibsPublishers\IRabbitMqPublisher::class,
			$rabbitPublisher
		);

		$handler = $this->getContainer()->getByType(Consumers\PropertyDataMessageHandler::class);

		$handler->process($routingKey, $message);
	}

}

$test_case = new PropertyDataMessageHandlerTest();
$test_case->run();
