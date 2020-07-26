<?php declare(strict_types = 1);

/**
 * ActionEntitySubscriber.php
 *
 * @license        More in license.md
 * @copyright      https://fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:TriggersNode!
 * @subpackage     Events
 * @since          0.1.0
 *
 * @date           05.04.20
 */

namespace FastyBird\TriggersNode\Subscribers;

use Doctrine\Common;
use Doctrine\ORM;
use FastyBird\TriggersNode\Entities;
use FastyBird\TriggersNode\Exceptions;
use Nette;

/**
 * Trigger action entity listener
 *
 * @package        FastyBird:TriggersNode!
 * @subpackage     Events
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class ActionEntitySubscriber implements Common\EventSubscriber
{

	use Nette\SmartObject;

	/**
	 * Register events
	 *
	 * @return string[]
	 */
	public function getSubscribedEvents(): array
	{
		return [
			ORM\Events::onFlush,
		];
	}

	/**
	 * @param ORM\Event\OnFlushEventArgs $eventArgs
	 *
	 * @return void
	 */
	public function onFlush(ORM\Event\OnFlushEventArgs $eventArgs): void
	{
		$em = $eventArgs->getEntityManager();
		$uow = $em->getUnitOfWork();

		// Check all scheduled updates
		foreach (array_merge($uow->getScheduledEntityInsertions(), $uow->getScheduledEntityUpdates()) as $object) {
			if ($object instanceof Entities\Actions\IChannelPropertyAction) {
				$trigger = $object->getTrigger();

				foreach ($trigger->getActions() as $action) {
					if (!$action->getId()->equals($object->getId())) {
						if (
							$action instanceof Entities\Actions\IChannelPropertyAction
							&& $action->getDevice() === $object->getDevice()
							&& $action->getChannel() === $object->getChannel()
							&& $action->getProperty() === $object->getProperty()
						) {
							throw new Exceptions\UniqueActionConstraint('Not same property in trigger actions');
						}
					}
				}
			}
		}
	}

}
