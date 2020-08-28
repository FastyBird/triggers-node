<?php declare(strict_types = 1);

/**
 * EntitiesSubscriber.php
 *
 * @license        More in license.md
 * @copyright      https://fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:TriggersNode!
 * @subpackage     Subscribers
 * @since          1.0.0
 *
 * @date           28.08.20
 */

namespace FastyBird\TriggersNode\Subscribers;

use Doctrine\Common;
use Doctrine\ORM;
use Doctrine\Persistence;
use FastyBird\NodeDatabase\Entities as NodeDatabaseEntities;
use FastyBird\NodeExchange\Publishers as NodeExchangePublishers;
use FastyBird\TriggersNode;
use IPub\DoctrineCrud;
use Nette;

/**
 * Doctrine entities events
 *
 * @package        FastyBird:TriggersNode!
 * @subpackage     Subscribers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class EntitiesSubscriber implements Common\EventSubscriber
{

	private const ACTION_CREATED = 'created';
	private const ACTION_UPDATED = 'updated';
	private const ACTION_DELETED = 'deleted';

	use Nette\SmartObject;

	/** @var NodeExchangePublishers\IRabbitMqPublisher */
	private $publisher;

	/** @var ORM\EntityManagerInterface */
	private $entityManager;

	public function __construct(
		NodeExchangePublishers\IRabbitMqPublisher $publisher,
		ORM\EntityManagerInterface $entityManager
	) {
		$this->publisher = $publisher;
		$this->entityManager = $entityManager;
	}

	/**
	 * Register events
	 *
	 * @return string[]
	 */
	public function getSubscribedEvents(): array
	{
		return [
			ORM\Events::onFlush,
			ORM\Events::postPersist,
			ORM\Events::postUpdate,
		];
	}

	/**
	 * @param ORM\Event\LifecycleEventArgs $eventArgs
	 *
	 * @return void
	 */
	public function postPersist(ORM\Event\LifecycleEventArgs $eventArgs): void
	{
		// onFlush was executed before, everything already initialized
		$entity = $eventArgs->getObject();

		// Check for valid entity
		if (!$entity instanceof NodeDatabaseEntities\IEntity) {
			return;
		}

		$this->processEntityAction($entity, self::ACTION_CREATED);
	}

	/**
	 * @param ORM\Event\LifecycleEventArgs $eventArgs
	 *
	 * @return void
	 */
	public function postUpdate(ORM\Event\LifecycleEventArgs $eventArgs): void
	{
		$uow = $this->entityManager->getUnitOfWork();

		// onFlush was executed before, everything already initialized
		$entity = $eventArgs->getObject();

		// Get changes => should be already computed here (is a listener)
		$changeset = $uow->getEntityChangeSet($entity);

		// If we have no changes left => don't create revision log
		if (count($changeset) === 0) {
			return;
		}

		// Check for valid entity
		if (
			!$entity instanceof NodeDatabaseEntities\IEntity
			|| $uow->isScheduledForDelete($entity)
		) {
			return;
		}

		$this->processEntityAction($entity, self::ACTION_UPDATED);
	}

	/**
	 * @return void
	 */
	public function onFlush(): void
	{
		$uow = $this->entityManager->getUnitOfWork();

		$processedEntities = [];

		$processEntities = [];

		foreach ($uow->getScheduledEntityDeletions() as $entity) {
			// Doctrine is fine deleting elements multiple times. We are not.
			$hash = $this->getHash($entity, $uow->getEntityIdentifier($entity));

			if (in_array($hash, $processedEntities, true)) {
				continue;
			}

			$processedEntities[] = $hash;

			// Check for valid entity
			if (!$entity instanceof NodeDatabaseEntities\IEntity) {
				continue;
			}

			$processEntities[] = $entity;
		}

		foreach ($processEntities as $entity) {
			$this->processEntityAction($entity, self::ACTION_DELETED);
		}
	}

	/**
	 * @param DoctrineCrud\Entities\IIdentifiedEntity $entity
	 * @param mixed[] $identifier
	 *
	 * @return string
	 */
	private function getHash(DoctrineCrud\Entities\IIdentifiedEntity $entity, array $identifier): string
	{
		return implode(
			' ',
			array_merge(
				[$this->getRealClass(get_class($entity))],
				$identifier
			)
		);
	}

	/**
	 * @param string $class
	 *
	 * @return string
	 */
	private function getRealClass(string $class): string
	{
		$pos = strrpos($class, '\\' . Persistence\Proxy::MARKER . '\\');

		if ($pos === false) {
			return $class;
		}

		return substr($class, $pos + Persistence\Proxy::MARKER_LENGTH + 2);
	}

	/**
	 * @param NodeDatabaseEntities\IEntity $entity
	 * @param string $action
	 *
	 * @return void
	 */
	private function processEntityAction(NodeDatabaseEntities\IEntity $entity, string $action): void
	{
		foreach (TriggersNode\Constants::RABBIT_MQ_ENTITIES_ROUTING_KEYS_MAPPING as $class => $routingKey) {
			if (
				$this->validateEntity($entity, $class)
				&& method_exists($entity, 'toArray')
			) {
				$routingKey = str_replace(TriggersNode\Constants::RABBIT_MQ_ENTITIES_ROUTING_KEY_ACTION_REPLACE_STRING, $action, $routingKey);

				$this->publisher->publish($routingKey, $entity->toArray());

				return;
			}
		}
	}

	/**
	 * @param NodeDatabaseEntities\IEntity $entity
	 * @param string $class
	 *
	 * @return bool
	 */
	private function validateEntity(NodeDatabaseEntities\IEntity $entity, string $class): bool
	{
		$result = false;

		if (get_class($entity) === $class) {
			$result = true;
		}

		if (is_subclass_of($entity, $class)) {
			$result = true;
		}

		return $result;
	}

}
