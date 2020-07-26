<?php declare(strict_types = 1);

namespace Tests\Cases;

use Doctrine\ORM;
use FastyBird\TriggersNode\Entities;
use FastyBird\TriggersNode\Subscribers;
use Mockery;
use Ninjify\Nunjuck\TestCase\BaseMockeryTestCase;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class NotificationEntitySubscriberTest extends BaseMockeryTestCase
{

	public function testSubscriberEvents(): void
	{
		$subscriber = new Subscribers\ActionEntitySubscriber();

		Assert::same(['onFlush'], $subscriber->getSubscribedEvents());
	}

	public function testCreateEntity(): void
	{
		$actions = [];

		$trigger = Mockery::mock(Entities\Triggers\Trigger::class);
		$trigger
			->shouldReceive('getNotifications')
			->withNoArgs()
			->andReturn($actions)
			->times(1);

		$entity = new Entities\Notifications\EmailNotification(
			'john@doe.com',
			$trigger
		);

		$uow = Mockery::mock(ORM\UnitOfWork::class);
		$uow
			->shouldReceive('getScheduledEntityInsertions')
			->withNoArgs()
			->andReturn([$entity])
			->times(1)
			->getMock()
			->shouldReceive('getScheduledEntityUpdates')
			->withNoArgs()
			->andReturn([])
			->times(1);

		$em = Mockery::mock(ORM\EntityManagerInterface::class);
		$em
			->shouldReceive('getUnitOfWork')
			->withNoArgs()
			->andReturn($uow)
			->times(1);

		$eventArgs = Mockery::mock(ORM\Event\OnFlushEventArgs::class);
		$eventArgs
			->shouldReceive('getEntityManager')
			->withNoArgs()
			->andReturn($em)
			->times(1);

		$subscriber = new Subscribers\NotificationEntitySubscriber();
		$subscriber->onFlush($eventArgs);

		Assert::true(true);
	}

	/**
	 * @throws FastyBird\TriggersNode\Exceptions\UniqueNotificationEmailConstraint
	 */
	public function testCreateEntityFailed(): void
	{
		$trigger = Mockery::mock(Entities\Triggers\Trigger::class);

		$storedEntity = new Entities\Notifications\EmailNotification(
			'john@doe.com',
			$trigger
		);

		$actions = [$storedEntity];

		$trigger
			->shouldReceive('getNotifications')
			->withNoArgs()
			->andReturn($actions)
			->times(1);

		$entity = new Entities\Notifications\EmailNotification(
			'john@doe.com',
			$trigger
		);

		$uow = Mockery::mock(ORM\UnitOfWork::class);
		$uow
			->shouldReceive('getScheduledEntityInsertions')
			->withNoArgs()
			->andReturn([$entity])
			->times(1)
			->getMock()
			->shouldReceive('getScheduledEntityUpdates')
			->withNoArgs()
			->andReturn([])
			->times(1);

		$em = Mockery::mock(ORM\EntityManagerInterface::class);
		$em
			->shouldReceive('getUnitOfWork')
			->withNoArgs()
			->andReturn($uow)
			->times(1);

		$eventArgs = Mockery::mock(ORM\Event\OnFlushEventArgs::class);
		$eventArgs
			->shouldReceive('getEntityManager')
			->withNoArgs()
			->andReturn($em)
			->times(1);

		$subscriber = new Subscribers\NotificationEntitySubscriber();
		$subscriber->onFlush($eventArgs);
	}

}

$test_case = new NotificationEntitySubscriberTest();
$test_case->run();
