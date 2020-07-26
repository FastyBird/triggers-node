<?php declare(strict_types = 1);

/**
 * IActionRepository.php
 *
 * @license        More in license.md
 * @copyright      https://fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:TriggersNode!
 * @subpackage     Models
 * @since          0.1.0
 *
 * @date           04.04.20
 */

namespace FastyBird\TriggersNode\Models\Actions;

use FastyBird\TriggersNode\Entities;
use FastyBird\TriggersNode\Queries;
use IPub\DoctrineOrmQuery;

/**
 * Action repository interface
 *
 * @package        FastyBird:TriggersNode!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IActionRepository
{

	/**
	 * @param Queries\FindActionsQuery $queryObject
	 * @param string $type
	 *
	 * @return Entities\Actions\IAction|null
	 *
	 * @phpstan-template T of Entities\Actions\Action
	 * @phpstan-param    Queries\FindActionsQuery<T> $queryObject
	 * @phpstan-param    class-string<T> $type
	 */
	public function findOneBy(
		Queries\FindActionsQuery $queryObject,
		string $type = Entities\Actions\Action::class
	): ?Entities\Actions\IAction;

	/**
	 * @param Queries\FindActionsQuery $queryObject
	 * @param string $type
	 *
	 * @return Entities\Actions\IAction[]
	 *
	 * @phpstan-template T of Entities\Actions\Action
	 * @phpstan-param    Queries\FindActionsQuery<T> $queryObject
	 * @phpstan-param    class-string<T> $type
	 */
	public function findAllBy(
		Queries\FindActionsQuery $queryObject,
		string $type = Entities\Actions\Action::class
	): array;

	/**
	 * @param Queries\FindActionsQuery $queryObject
	 * @param string $type
	 *
	 * @return DoctrineOrmQuery\ResultSet
	 *
	 * @phpstan-template T of Entities\Actions\Action
	 * @phpstan-param    Queries\FindActionsQuery<T> $queryObject
	 * @phpstan-param    class-string<T> $type
	 * @phpstan-return   DoctrineOrmQuery\ResultSet<T>
	 */
	public function getResultSet(
		Queries\FindActionsQuery $queryObject,
		string $type = Entities\Actions\Action::class
	): DoctrineOrmQuery\ResultSet;

}
