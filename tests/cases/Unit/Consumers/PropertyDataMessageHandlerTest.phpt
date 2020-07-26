<?php declare(strict_types = 1);

namespace Tests\Cases;

use FastyBird\NodeExchange\Publishers as NodeExchangePublishers;
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
final class PropertyDataMessageHandlerTest extends DbTestCase
{

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

		$handler = $this->getContainer()->getByType(Consumers\PropertyDataMessageHandler::class);

		$handler->process($routingKey, $message);
	}

}

$test_case = new PropertyDataMessageHandlerTest();
$test_case->run();
