<?php declare(strict_types = 1);

/**
 * FindConditionsQuery.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:TriggersNode!
 * @subpackage     Queries
 * @since          0.1.0
 *
 * @date           04.04.20
 */

namespace FastyBird\TriggersNode\Queries;

use Closure;
use Doctrine\ORM;
use FastyBird\TriggersNode\Entities;
use FastyBird\TriggersNode\Exceptions;
use FastyBird\TriggersNode\Types;
use IPub\DoctrineOrmQuery;
use Ramsey\Uuid;

/**
 * Find conditions entities query
 *
 * @package          FastyBird:TriggersNode!
 * @subpackage       Queries
 *
 * @author           Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @phpstan-template T of Entities\Conditions\Condition
 * @phpstan-extends  DoctrineOrmQuery\QueryObject<T>
 */
class FindConditionsQuery extends DoctrineOrmQuery\QueryObject
{

	/** @var Closure[] */
	private $filter = [];

	/** @var Closure[] */
	private $select = [];

	/**
	 * @param Uuid\UuidInterface $id
	 *
	 * @return void
	 */
	public function byId(Uuid\UuidInterface $id): void
	{
		$this->filter[] = function (ORM\QueryBuilder $qb) use ($id): void {
			$qb->andWhere('c.id = :id')->setParameter('id', $id, Uuid\Doctrine\UuidBinaryType::NAME);
		};
	}

	/**
	 * @param Entities\Triggers\ITrigger $trigger
	 *
	 * @return void
	 */
	public function forTrigger(Entities\Triggers\ITrigger $trigger): void
	{
		$this->filter[] = function (ORM\QueryBuilder $qb) use ($trigger): void {
			$qb->andWhere('trigger.id = :trigger')->setParameter('trigger', $trigger->getId(), Uuid\Doctrine\UuidBinaryType::NAME);
		};
	}

	/**
	 * @param string $device
	 *
	 * @return void
	 */
	public function forDevice(string $device): void
	{
		$this->filter[] = function (ORM\QueryBuilder $qb) use ($device): void {
			$qb->andWhere('c.device = :device')->setParameter('device', $device);
		};
	}

	/**
	 * @param string $channel
	 *
	 * @return void
	 */
	public function forChannel(string $channel): void
	{
		$this->filter[] = function (ORM\QueryBuilder $qb) use ($channel): void {
			$qb->andWhere('c.channel = :channel')->setParameter('channel', $channel);
		};
	}

	/**
	 * @param string $property
	 *
	 * @return void
	 */
	public function forProperty(string $property): void
	{
		$this->filter[] = function (ORM\QueryBuilder $qb) use ($property): void {
			$qb->andWhere('c.property = :property')->setParameter('property', $property);
		};
	}

	/**
	 * @param string $value
	 * @param string $operator
	 *
	 * @return void
	 */
	public function withPropertyValue(
		string $value,
		string $operator = Types\ConditionOperatorType::STATE_VALUE_EQUAL
	): void {
		if (!Types\ConditionOperatorType::isValidValue($operator)) {
			throw new Exceptions\InvalidArgumentException('Invalid operator given');
		}

		$this->filter[] = function (ORM\QueryBuilder $qb) use ($operator): void {
			$qb->andWhere('c.operator = :operator')->setParameter('operator', $operator);
		};

		$this->filter[] = function (ORM\QueryBuilder $qb) use ($value): void {
			$qb->andWhere('c.operand = :operand')->setParameter('operand', $value);
		};
	}

	/**
	 * @param float $value
	 * @param float|null $previousValue
	 *
	 * @return void
	 */
	public function byValue(float $value, ?float $previousValue = null): void
	{
		$this->filter[] = function (ORM\QueryBuilder $qb) use ($value, $previousValue): void {
			if ($previousValue !== null) {
				$qb
					->andWhere(
						'(previousValue <= c.operand AND c.operand < :value AND c.operator = :operatorAbove)'
						. ' OR '
						. '(previousValue >= c.operand AND c.operand > :value AND c.operator = :operatorBelow)'
						. ' OR '
						. '(previousValue <> c.operand AND c.operand = :value AND c.operator = :operatorEqual)'
					)
					->setParameter('value', $value)
					->setParameter('previousValue', $previousValue)
					->setParameter('operatorAbove', Types\ConditionOperatorType::STATE_VALUE_ABOVE)
					->setParameter('operatorBelow', Types\ConditionOperatorType::STATE_VALUE_BELOW)
					->setParameter('operatorEqual', Types\ConditionOperatorType::STATE_VALUE_EQUAL);

			} else {
				$qb
					->andWhere(
						'(c.operand < :value AND c.operator = :operatorAbove)'
						. ' OR '
						. '(c.operand > :value AND c.operator = :operatorBelow)'
						. ' OR '
						. '(c.operand = :value AND c.operator = :operatorEqual)'
					)
					->setParameter('value', $value)
					->setParameter('operatorAbove', Types\ConditionOperatorType::STATE_VALUE_ABOVE)
					->setParameter('operatorBelow', Types\ConditionOperatorType::STATE_VALUE_BELOW)
					->setParameter('operatorEqual', Types\ConditionOperatorType::STATE_VALUE_EQUAL);
			}
		};
	}

	/**
	 * @param float $value
	 * @param float $previousValue
	 *
	 * @return void
	 */
	public function byValueAbove(float $value, float $previousValue): void
	{
		$this->filter[] = function (ORM\QueryBuilder $qb) use ($value, $previousValue): void {
			$qb
				->andWhere('c.operand >= :previousValue AND c.operand < :value AND c.operator = :operator')
				->setParameter('value', $value)
				->setParameter('previousValue', $previousValue)
				->setParameter('operator', Types\ConditionOperatorType::STATE_VALUE_ABOVE);
		};
	}

	/**
	 * @param float $value
	 * @param float $previousValue
	 *
	 * @return void
	 */
	public function byValueBelow(float $value, float $previousValue): void
	{
		$this->filter[] = function (ORM\QueryBuilder $qb) use ($value, $previousValue): void {
			$qb
				->andWhere('c.operand <= :previousValue AND c.operand > :value AND c.operator = :operator')
				->setParameter('value', $value)
				->setParameter('previousValue', $previousValue)
				->setParameter('operator', Types\ConditionOperatorType::STATE_VALUE_BELOW);
		};
	}

	/**
	 * @return void
	 */
	public function onlyEnabledTriggers(): void
	{
		$this->select[] = function (ORM\QueryBuilder $qb): void {
			$qb->join(Entities\Conditions\Condition::class, 'ac', ORM\Query\Expr\Join::WITH, 'c = ac');
			$qb->join('ac.trigger', 'tr');
		};

		$this->filter[] = function (ORM\QueryBuilder $qb): void {
			$qb->andWhere('tr.enabled = :enabled')->setParameter('enabled', true);
		};
	}

	/**
	 * @param ORM\EntityRepository<Entities\Conditions\Condition> $repository
	 *
	 * @return ORM\QueryBuilder
	 *
	 * @phpstan-param ORM\EntityRepository<T> $repository
	 */
	protected function doCreateQuery(ORM\EntityRepository $repository): ORM\QueryBuilder
	{
		$qb = $this->createBasicDql($repository);

		foreach ($this->select as $modifier) {
			$modifier($qb);
		}

		return $qb;
	}

	/**
	 * @param ORM\EntityRepository<Entities\Conditions\Condition> $repository
	 *
	 * @return ORM\QueryBuilder
	 *
	 * @phpstan-param ORM\EntityRepository<T> $repository
	 */
	protected function doCreateCountQuery(ORM\EntityRepository $repository): ORM\QueryBuilder
	{
		$qb = $this->createBasicDql($repository)->select('COUNT(c.id)');

		foreach ($this->select as $modifier) {
			$modifier($qb);
		}

		return $qb;
	}

	/**
	 * @param ORM\EntityRepository<Entities\Conditions\Condition> $repository
	 *
	 * @return ORM\QueryBuilder
	 *
	 * @phpstan-param ORM\EntityRepository<T> $repository
	 */
	private function createBasicDql(ORM\EntityRepository $repository): ORM\QueryBuilder
	{
		$qb = $repository->createQueryBuilder('c');
		$qb->addSelect('trigger');
		$qb->join('c.trigger', 'trigger');

		foreach ($this->filter as $modifier) {
			$modifier($qb);
		}

		return $qb;
	}

}
