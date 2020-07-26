<?php declare(strict_types = 1);

/**
 * ITriggerRepository.php
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

namespace FastyBird\TriggersNode\Models\Triggers;

use FastyBird\TriggersNode\Entities;
use FastyBird\TriggersNode\Queries;
use IPub\DoctrineOrmQuery;

/**
 * Trigger repository interface
 *
 * @package        FastyBird:TriggersNode!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface ITriggerRepository
{

	/**
	 * @param Queries\FindTriggersQuery $queryObject
	 * @param string $type
	 *
	 * @return Entities\Triggers\ITrigger|null
	 *
	 * @phpstan-template T of Entities\Triggers\Trigger
	 * @phpstan-param    Queries\FindTriggersQuery<T> $queryObject
	 * @phpstan-param    class-string<T> $type
	 */
	public function findOneBy(
		Queries\FindTriggersQuery $queryObject,
		string $type = Entities\Triggers\Trigger::class
	): ?Entities\Triggers\ITrigger;

	/**
	 * @param Queries\FindTriggersQuery $queryObject
	 * @param string $type
	 *
	 * @return Entities\Triggers\ITrigger[]
	 *
	 * @phpstan-template T of Entities\Triggers\Trigger
	 * @phpstan-param    Queries\FindTriggersQuery<T> $queryObject
	 * @phpstan-param    class-string<T> $type
	 */
	public function findAllBy(
		Queries\FindTriggersQuery $queryObject,
		string $type = Entities\Triggers\Trigger::class
	): array;

	/**
	 * @param Queries\FindTriggersQuery $queryObject
	 * @param string $type
	 *
	 * @return DoctrineOrmQuery\ResultSet
	 *
	 * @phpstan-template T of Entities\Triggers\Trigger
	 * @phpstan-param    Queries\FindTriggersQuery<T> $queryObject
	 * @phpstan-param    class-string<T> $type
	 * @phpstan-return   DoctrineOrmQuery\ResultSet<T>
	 */
	public function getResultSet(
		Queries\FindTriggersQuery $queryObject,
		string $type = Entities\Triggers\Trigger::class
	): DoctrineOrmQuery\ResultSet;

}
