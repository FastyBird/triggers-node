<?php declare(strict_types = 1);

namespace Tests\Cases;

use FastyBird\NodeBootstrap\Boot;
use FastyBird\TriggersNode\Consumers;
use FastyBird\TriggersNode\Controllers;
use FastyBird\TriggersNode\Hydrators;
use FastyBird\TriggersNode\Models;
use FastyBird\TriggersNode\Schemas;
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

		Assert::notNull($container->getByType(Consumers\DeviceMessageHandler::class));
		Assert::notNull($container->getByType(Consumers\DevicePropertyMessageHandler::class));
		Assert::notNull($container->getByType(Consumers\ChannelMessageHandler::class));
		Assert::notNull($container->getByType(Consumers\ChannelPropertyMessageHandler::class));
		Assert::notNull($container->getByType(Consumers\PropertyDataMessageHandler::class));

		Assert::notNull($container->getByType(Models\Triggers\TriggerRepository::class));
		Assert::notNull($container->getByType(Models\Actions\ActionRepository::class));
		Assert::notNull($container->getByType(Models\Notifications\NotificationRepository::class));
		Assert::notNull($container->getByType(Models\Conditions\ConditionRepository::class));

		Assert::notNull($container->getByType(Models\Triggers\TriggersManager::class));
		Assert::notNull($container->getByType(Models\Actions\ActionsManager::class));
		Assert::notNull($container->getByType(Models\Notifications\NotificationsManager::class));
		Assert::notNull($container->getByType(Models\Conditions\ConditionsManager::class));

		Assert::notNull($container->getByType(Controllers\TriggersV1Controller::class));
		Assert::notNull($container->getByType(Controllers\ActionsV1Controller::class));
		Assert::notNull($container->getByType(Controllers\NotificationsV1Controller::class));
		Assert::notNull($container->getByType(Controllers\ConditionsV1Controller::class));

		Assert::notNull($container->getByType(Schemas\Triggers\AutomaticTriggerSchema::class));
		Assert::notNull($container->getByType(Schemas\Triggers\ManualTriggerSchema::class));
		Assert::notNull($container->getByType(Schemas\Triggers\ChannelPropertyTriggerSchema::class));
		Assert::notNull($container->getByType(Schemas\Actions\ChannelPropertyActionSchema::class));
		Assert::notNull($container->getByType(Schemas\Notifications\EmailNotificationSchema::class));
		Assert::notNull($container->getByType(Schemas\Notifications\SmsNotificationSchema::class));
		Assert::notNull($container->getByType(Schemas\Conditions\ChannelPropertyConditionSchema::class));
		Assert::notNull($container->getByType(Schemas\Conditions\DevicePropertyConditionSchema::class));
		Assert::notNull($container->getByType(Schemas\Conditions\DateConditionSchema::class));
		Assert::notNull($container->getByType(Schemas\Conditions\TimeConditionSchema::class));

		Assert::notNull($container->getByType(Hydrators\Triggers\AutomaticTriggerHydrator::class));
		Assert::notNull($container->getByType(Hydrators\Triggers\ManualTriggerHydrator::class));
		Assert::notNull($container->getByType(Hydrators\Triggers\ChannelPropertyTriggerHydrator::class));
		Assert::notNull($container->getByType(Hydrators\Actions\ChannelPropertyActionHydrator::class));
		Assert::notNull($container->getByType(Hydrators\Notifications\EmailNotificationHydrator::class));
		Assert::notNull($container->getByType(Hydrators\Notifications\SmsNotificationHydrator::class));
		Assert::notNull($container->getByType(Hydrators\Conditions\ChannelPropertyConditionHydrator::class));
		Assert::notNull($container->getByType(Hydrators\Conditions\DevicePropertyConditionHydrator::class));
		Assert::notNull($container->getByType(Hydrators\Conditions\TimeConditionHydrator::class));
	}

}

$test_case = new ServicesTest();
$test_case->run();
