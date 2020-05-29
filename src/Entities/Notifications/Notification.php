<?php declare(strict_types = 1);

/**
 * Notification.php
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

use Doctrine\ORM\Mapping as ORM;
use FastyBird\NodeDatabase\Entities as NodeDatabaseEntities;
use FastyBird\TriggersNode\Entities;
use IPub\DoctrineCrud\Mapping\Annotation as IPubDoctrine;
use IPub\DoctrineTimestampable;
use Ramsey\Uuid;
use Throwable;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="fb_notifications",
 *     options={
 *       "collate"="utf8mb4_general_ci",
 *       "charset"="utf8mb4",
 *       "comment"="Triggers notifications"
 *     }
 * )
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="notification_type", type="string", length=20)
 * @ORM\DiscriminatorMap({
 *    "sms"     = "FastyBird\TriggersNode\Entities\Notifications\SmsNotification",
 *    "email"   = "FastyBird\TriggersNode\Entities\Notifications\EmailNotification",
 * })
 * @ORM\MappedSuperclass
 */
abstract class Notification extends NodeDatabaseEntities\Entity implements INotification
{

	use DoctrineTimestampable\Entities\TEntityCreated;
	use DoctrineTimestampable\Entities\TEntityUpdated;

	/**
	 * @var Uuid\UuidInterface
	 *
	 * @ORM\Id
	 * @ORM\Column(type="uuid_binary", name="notification_id")
	 * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
	 */
	protected $id;

	/**
	 * @var Entities\Triggers\ITrigger
	 *
	 * @IPubDoctrine\Crud(is="required")
	 * @ORM\ManyToOne(targetEntity="FastyBird\TriggersNode\Entities\Triggers\Trigger", inversedBy="actions")
	 * @ORM\JoinColumn(name="trigger_id", referencedColumnName="trigger_id", onDelete="CASCADE")
	 */
	protected $trigger;

	/**
	 * @param Entities\Triggers\ITrigger $trigger
	 * @param Uuid\UuidInterface|null $id
	 *
	 * @throws Throwable
	 */
	public function __construct(
		Entities\Triggers\ITrigger $trigger,
		?Uuid\UuidInterface $id = null
	) {
		$this->id = $id ?? Uuid\Uuid::uuid4();

		$this->trigger = $trigger;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTrigger(): Entities\Triggers\ITrigger
	{
		return $this->trigger;
	}

}
