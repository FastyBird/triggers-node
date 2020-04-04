<?php declare(strict_types = 1);

/**
 * FindActionsQuery.php
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
use IPub\DoctrineOrmQuery;
use Ramsey\Uuid;

/**
 * Find action entities query
 *
 * @package          FastyBird:TriggersNode!
 * @subpackage       Queries
 *
 * @author           Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @phpstan-template T of Entities\Actions\Action
 * @phpstan-extends  DoctrineOrmQuery\QueryObject<T>
 */
class FindActionsQuery extends DoctrineOrmQuery\QueryObject
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
			$qb->andWhere('a.id = :id')->setParameter('id', $id, Uuid\Doctrine\UuidBinaryType::NAME);
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
	 * @param string $channel
	 *
	 * @return void
	 */
	public function forChannel(string $channel): void
	{
		$this->filter[] = function (ORM\QueryBuilder $qb) use ($channel): void {
			$qb->andWhere('a.channel = :channel')->setParameter('channel', $channel);
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
			$qb->andWhere('a.property = :property')->setParameter('property', $property);
		};
	}

	/**
	 * @param ORM\EntityRepository<Entities\Actions\Action> $repository
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
	 * @param ORM\EntityRepository<Entities\Actions\Action> $repository
	 *
	 * @return ORM\QueryBuilder
	 *
	 * @phpstan-param ORM\EntityRepository<T> $repository
	 */
	protected function doCreateCountQuery(ORM\EntityRepository $repository): ORM\QueryBuilder
	{
		$qb = $this->createBasicDql($repository)->select('COUNT(a.id)');

		foreach ($this->select as $modifier) {
			$modifier($qb);
		}

		return $qb;
	}

	/**
	 * @param ORM\EntityRepository<Entities\Actions\Action> $repository
	 *
	 * @return ORM\QueryBuilder
	 *
	 * @phpstan-param ORM\EntityRepository<T> $repository
	 */
	private function createBasicDql(ORM\EntityRepository $repository): ORM\QueryBuilder
	{
		$qb = $repository->createQueryBuilder('a');
		$qb->addSelect('trigger');
		$qb->join('a.trigger', 'trigger');

		foreach ($this->filter as $modifier) {
			$modifier($qb);
		}

		return $qb;
	}

}
