<?php declare(strict_types = 1);

namespace Tests\Cases;

use FastyBird\Bootstrap\Boot;
use FastyBird\TriggersNode\Commands;
use FastyBird\TriggersNode\Consumers;
use FastyBird\TriggersNode\Events;
use FastyBird\TriggersNode\Subscribers;
use Ninjify\Nunjuck\TestCase\BaseTestCase;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class ServicesTest extends BaseTestCase
{

	public function testServicesRegistration(): void
	{
		$configurator = Boot\Bootstrap::boot();
		$configurator->addParameters([
			'database' => [
				'driver' => 'pdo_sqlite',
			],
		]);

		$container = $configurator->createContainer();

		Assert::notNull($container->getByType(Commands\InitializeCommand::class));

		Assert::notNull($container->getByType(Consumers\DeviceMessageHandler::class));
		Assert::notNull($container->getByType(Consumers\DevicePropertyMessageHandler::class));
		Assert::notNull($container->getByType(Consumers\ChannelMessageHandler::class));
		Assert::notNull($container->getByType(Consumers\ChannelPropertyMessageHandler::class));

		Assert::notNull($container->getByType(Events\AfterConsumeHandler::class));
		Assert::notNull($container->getByType(Events\ServerBeforeStartHandler::class));

		Assert::notNull($container->getByType(Subscribers\EntitiesSubscriber::class));
	}

}

$test_case = new ServicesTest();
$test_case->run();
