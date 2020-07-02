<?php declare(strict_types = 1);

/**
 * ActionRepository.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:TriggersNode!
 * @subpackage     Models
 * @since          0.1.0
 *
 * @date           04.04.20
 */

namespace FastyBird\TriggersNode\Models\Actions;

use Doctrine\Common;
use Doctrine\Persistence;
use FastyBird\TriggersNode\Entities;
use FastyBird\TriggersNode\Exceptions;
use FastyBird\TriggersNode\Queries;
use IPub\DoctrineOrmQuery;
use Nette;
use Throwable;

/**
 * Action repository
 *
 * @package        FastyBird:TriggersNode!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class ActionRepository implements IActionRepository
{

	use Nette\SmartObject;

	/** @var Common\Persistence\ManagerRegistry */
	private $managerRegistry;

	/** @var Persistence\ObjectRepository<Entities\Actions\Action>[] */
	private $repository = [];

	public function __construct(Common\Persistence\ManagerRegistry $managerRegistry)
	{
		$this->managerRegistry = $managerRegistry;
	}

	/**
	 * {@inheritDoc}
	 */
	public function findOneBy(
		Queries\FindActionsQuery $queryObject,
		string $type = Entities\Actions\Action::class
	): ?Entities\Actions\IAction {
		/** @var Entities\Actions\IAction|null $action */
		$action = $queryObject->fetchOne($this->getRepository($type));

		return $action;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws Throwable
	 */
	public function findAllBy(
		Queries\FindActionsQuery $queryObject,
		string $type = Entities\Actions\Action::class
	): array {
		$result = $queryObject->fetch($this->getRepository($type));

		return is_array($result) ? $result : $result->toArray();
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws Throwable
	 */
	public function getResultSet(
		Queries\FindActionsQuery $queryObject,
		string $type = Entities\Actions\Action::class
	): DoctrineOrmQuery\ResultSet {
		$result = $queryObject->fetch($this->getRepository($type));

		if (!$result instanceof DoctrineOrmQuery\ResultSet) {
			throw new Exceptions\InvalidStateException('Result set for given query could not be loaded.');
		}

		return $result;
	}

	/**
	 * @param string $type
	 *
	 * @return Persistence\ObjectRepository<Entities\Actions\Action>
	 *
	 * @phpstan-template T of Entities\Actions\Action
	 * @phpstan-param    class-string<T> $type
	 */
	private function getRepository(string $type): Persistence\ObjectRepository
	{
		if (!isset($this->repository[$type])) {
			$this->repository[$type] = $this->managerRegistry->getRepository($type);
		}

		return $this->repository[$type];
	}

}
