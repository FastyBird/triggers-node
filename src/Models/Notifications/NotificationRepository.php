<?php declare(strict_types = 1);

/**
 * NotificationRepository.php
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

namespace FastyBird\TriggersNode\Models\Notifications;

use Doctrine\Common;
use Doctrine\ORM;
use FastyBird\TriggersNode\Entities;
use FastyBird\TriggersNode\Exceptions;
use FastyBird\TriggersNode\Queries;
use IPub\DoctrineOrmQuery;
use Nette;
use Throwable;

/**
 * Notification repository
 *
 * @package        FastyBird:TriggersNode!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class NotificationRepository implements INotificationRepository
{

	use Nette\SmartObject;

	/** @var Common\Persistence\ManagerRegistry */
	private $managerRegistry;

	/** @var ORM\EntityRepository<Entities\Notifications\Notification>[] */
	private $repository = [];

	public function __construct(Common\Persistence\ManagerRegistry $managerRegistry)
	{
		$this->managerRegistry = $managerRegistry;
	}

	/**
	 * {@inheritDoc}
	 */
	public function findOneBy(
		Queries\FindNotificationsQuery $queryObject,
		string $type = Entities\Notifications\Notification::class
	): ?Entities\Notifications\INotification {
		/** @var Entities\Notifications\INotification|null $notification */
		$notification = $queryObject->fetchOne($this->getRepository($type));

		return $notification;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws Throwable
	 */
	public function findAllBy(
		Queries\FindNotificationsQuery $queryObject,
		string $type = Entities\Notifications\Notification::class
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
		Queries\FindNotificationsQuery $queryObject,
		string $type = Entities\Notifications\Notification::class
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
	 * @return ORM\EntityRepository<Entities\Notifications\Notification>
	 *
	 * @phpstan-template T of Entities\Notifications\Notification
	 * @phpstan-param    class-string<T> $type
	 */
	private function getRepository(string $type): ORM\EntityRepository
	{
		if (!isset($this->repository[$type])) {
			$this->repository[$type] = $this->managerRegistry->getRepository($type);
		}

		return $this->repository[$type];
	}

}
