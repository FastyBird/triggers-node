<?php declare(strict_types = 1);

/**
 * INotification.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:TriggersNode!
 * @subpackage     Entities
 * @since          0.1.0
 *
 * @date           04.04.20
 */

namespace FastyBird\TriggersNode\Entities\Notifications;

use FastyBird\NodeDatabase\Entities as NodeDatabaseEntities;
use FastyBird\TriggersNode\Entities;
use IPub\DoctrineTimestampable;

/**
 * Base notification entity interface
 *
 * @package        FastyBird:TriggersNode!
 * @subpackage     Entities
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface INotification extends NodeDatabaseEntities\IEntity,
	DoctrineTimestampable\Entities\IEntityCreated, DoctrineTimestampable\Entities\IEntityUpdated
{

	/**
	 * @return Entities\Triggers\ITrigger
	 */
	public function getTrigger(): Entities\Triggers\ITrigger;

}
