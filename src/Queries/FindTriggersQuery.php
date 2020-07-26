<?php declare(strict_types = 1);

/**
 * FindTriggersQuery.php
 *
 * @license        More in license.md
 * @copyright      https://fastybird.com
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
use IPub\DoctrineOrmQuery;
use Ramsey\Uuid;

/**
 * Find trigger entities query
 *
 * @package          FastyBird:TriggersNode!
 * @subpackage       Queries
 *
 * @author           Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @phpstan-template T of Entities\Triggers\Trigger
 * @phpstan-extends  DoctrineOrmQuery\QueryObject<T>
 */
class FindTriggersQuery extends DoctrineOrmQuery\QueryObject
{

	/** @var Closure[] */
	protected $filter = [];

	/** @var Closure[] */
	protected $select = [];

	/**
	 * @param Uuid\UuidInterface $id
	 *
	 * @return void
	 */
	public function byId(Uuid\UuidInterface $id): void
	{
		$this->filter[] = function (ORM\QueryBuilder $qb) use ($id): void {
			$qb->andWhere('t.id = :id')->setParameter('id', $id, Uuid\Doctrine\UuidBinaryType::NAME);
		};
	}

	/**
	 * @return void
	 */
	public function withoutConditions(): void
	{
		$this->filter[] = function (ORM\QueryBuilder $qb): void {
			$qb->andWhere('SIZE(t.conditions) = 0');
		};
	}

	/**
	 * @return void
	 */
	public function withoutActions(): void
	{
		$this->filter[] = function (ORM\QueryBuilder $qb): void {
			$qb->andWhere('SIZE(t.actions) = 0');
		};
	}

	/**
	 * @return void
	 */
	public function onlyEnabled(): void
	{
		$this->filter[] = function (ORM\QueryBuilder $qb): void {
			$qb->andWhere('t.enabled = :enabled')->setParameter('enabled', true);
		};
	}

	/**
	 * @param ORM\EntityRepository<Entities\Triggers\Trigger> $repository
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
	 * @param ORM\EntityRepository<Entities\Triggers\Trigger> $repository
	 *
	 * @return ORM\QueryBuilder
	 *
	 * @phpstan-param ORM\EntityRepository<T> $repository
	 */
	protected function doCreateCountQuery(ORM\EntityRepository $repository): ORM\QueryBuilder
	{
		$qb = $this->createBasicDql($repository)->select('COUNT(t.id)');

		foreach ($this->select as $modifier) {
			$modifier($qb);
		}

		return $qb;
	}

	/**
	 * @param ORM\EntityRepository<Entities\Triggers\Trigger> $repository
	 *
	 * @return ORM\QueryBuilder
	 *
	 * @phpstan-param ORM\EntityRepository<T> $repository
	 */
	protected function createBasicDql(ORM\EntityRepository $repository): ORM\QueryBuilder
	{
		$qb = $repository->createQueryBuilder('t');

		foreach ($this->filter as $modifier) {
			$modifier($qb);
		}

		return $qb;
	}

}
