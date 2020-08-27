<?php declare(strict_types = 1);

/**
 * SmsNotification.php
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
use IPub\Phone;
use Ramsey\Uuid;
use Throwable;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="fb_notifications_sms",
 *     options={
 *       "collate"="utf8mb4_general_ci",
 *       "charset"="utf8mb4",
 *       "comment"="SMS notifications"
 *     }
 * )
 */
class SmsNotification extends Notification implements ISmsNotification
{

	/**
	 * @var Phone\Entities\Phone
	 *
	 * @IPubDoctrine\Crud(is={"required", "writable"})
	 * @ORM\Column(type="phone", name="notification_phone", length=150, nullable=false)
	 */
	private $phone;

	/**
	 * @param Phone\Entities\Phone $phone
	 * @param Entities\Triggers\ITrigger $trigger
	 * @param Uuid\UuidInterface|null $id
	 *
	 * @throws Throwable
	 */
	public function __construct(
		Phone\Entities\Phone $phone,
		Entities\Triggers\ITrigger $trigger,
		?Uuid\UuidInterface $id = null
	) {
		parent::__construct($trigger, $id);

		$this->phone = $phone;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setPhone(Phone\Entities\Phone $phone): void
	{
		$this->phone = $phone;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPhone(): Phone\Entities\Phone
	{
		return $this->phone;
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return array_merge(parent::toArray(), [
			'phone' => $this->getPhone()->getInternationalNumber(),
		]);
	}

}
