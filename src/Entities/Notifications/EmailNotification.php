<?php declare(strict_types = 1);

/**
 * EmailNotification.php
 *
 * @license        More in license.md
 * @copyright      https://fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:TriggersNode!
 * @subpackage     Entities
 * @since          0.1.0
 *
 * @date           04.04.20
 */

namespace FastyBird\TriggersNode\Entities\Notifications;

use Doctrine\ORM\Mapping as ORM;
use FastyBird\TriggersNode\Entities;
use IPub\DoctrineCrud\Mapping\Annotation as IPubDoctrine;
use Ramsey\Uuid;
use Throwable;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="fb_notifications_emails",
 *     options={
 *       "collate"="utf8mb4_general_ci",
 *       "charset"="utf8mb4",
 *       "comment"="Emails notifications"
 *     }
 * )
 */
class EmailNotification extends Notification implements IEmailNotification
{

	/**
	 * @var string
	 *
	 * @IPubDoctrine\Crud(is={"required", "writable"})
	 * @ORM\Column(type="string", name="notification_email", length=150, nullable=false)
	 */
	private $email;

	/**
	 * @param string $email
	 * @param Entities\Triggers\ITrigger $trigger
	 * @param Uuid\UuidInterface|null $id
	 *
	 * @throws Throwable
	 */
	public function __construct(
		string $email,
		Entities\Triggers\ITrigger $trigger,
		?Uuid\UuidInterface $id = null
	) {
		parent::__construct($trigger, $id);

		$this->email = $email;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setEmail(string $email): void
	{
		$this->email = $email;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getEmail(): string
	{
		return $this->email;
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return array_merge(parent::toArray(), [
			'email' => $this->getEmail(),
		]);
	}

}
