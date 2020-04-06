<?php declare(strict_types = 1);

namespace Tests\Cases;

use Doctrine\ORM;
use FastyBird\TriggersNode\Entities;
use FastyBird\TriggersNode\Subscribers;
use FastyBird\TriggersNode\Types;
use Mockery;
use Ninjify\Nunjuck\TestCase\BaseMockeryTestCase;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

final class ConditionEntitySubscriberTest extends BaseMockeryTestCase
{

	public function testSubscriberEvents(): void
	{
		$subscriber = new Subscribers\ActionEntitySubscriber();

		Assert::same(['onFlush'], $subscriber->getSubscribedEvents());
	}

	public function testCreateEntity(): void
	{
		$actions = [];

		$trigger = Mockery::mock(Entities\Triggers\IAutomaticTrigger::class);
		$trigger
			->shouldReceive('getConditions')
			->withNoArgs()
			->andReturn($actions)
			->times(1);

		$entity = new Entities\Conditions\ChannelPropertyCondition(
			'device-name',
			'channel-name',
			'property-name',
			Types\ConditionOperatorType::get(Types\ConditionOperatorType::STATE_VALUE_EQUAL),
			'10',
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

		$subscriber = new Subscribers\ConditionEntitySubscriber();
		$subscriber->onFlush($eventArgs);
	}

	/**
	 * @throws FastyBird\TriggersNode\Exceptions\UniqueConditionConstraint
	 */
	public function testCreateEntityFailed(): void
	{
		$trigger = Mockery::mock(Entities\Triggers\IAutomaticTrigger::class);

		$storedEntity = new Entities\Conditions\ChannelPropertyCondition(
			'device-name',
			'channel-name',
			'property-name',
			Types\ConditionOperatorType::get(Types\ConditionOperatorType::STATE_VALUE_EQUAL),
			'10',
			$trigger
		);

		$actions = [$storedEntity];

		$trigger
			->shouldReceive('getConditions')
			->withNoArgs()
			->andReturn($actions)
			->times(1);

		$entity = new Entities\Conditions\ChannelPropertyCondition(
			'device-name',
			'channel-name',
			'property-name',
			Types\ConditionOperatorType::get(Types\ConditionOperatorType::STATE_VALUE_EQUAL),
			'10',
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

		$subscriber = new Subscribers\ConditionEntitySubscriber();
		$subscriber->onFlush($eventArgs);
	}

}

$test_case = new ConditionEntitySubscriberTest();
$test_case->run();
