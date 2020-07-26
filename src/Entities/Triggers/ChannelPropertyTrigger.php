<?php declare(strict_types = 1);

/**
 * Trigger.php
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

namespace FastyBird\TriggersNode\Entities\Triggers;

use Consistence\Doctrine\Enum\EnumAnnotation as Enum;
use Doctrine\ORM\Mapping as ORM;
use FastyBird\TriggersNode\Entities;
use FastyBird\TriggersNode\Exceptions;
use FastyBird\TriggersNode\Types;
use IPub\DoctrineCrud\Mapping\Annotation as IPubDoctrine;
use Ramsey\Uuid;
use Throwable;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="fb_triggers_channel_property",
 *     options={
 *       "collate"="utf8mb4_general_ci",
 *       "charset"="utf8mb4",
 *       "comment"="Physical devices triggers"
 *     },
 *     uniqueConstraints={
 *       @ORM\UniqueConstraint(name="trigger_unique", columns={"trigger_channel", "trigger_property", "trigger_operand"})
 *     },
 *     indexes={
 *       @ORM\Index(name="trigger_device_idx", columns={"trigger_device"}),
 *       @ORM\Index(name="trigger_channel_idx", columns={"trigger_channel"}),
 *       @ORM\Index(name="trigger_property_idx", columns={"trigger_property"})
 *     }
 * )
 */
class ChannelPropertyTrigger extends Trigger implements IChannelPropertyTrigger
{

	/**
	 * @var string
	 *
	 * @IPubDoctrine\Crud(is="required")
	 * @ORM\Column(type="string", name="trigger_device", length=100, nullable=false)
	 */
	private $device;

	/**
	 * @var string
	 *
	 * @IPubDoctrine\Crud(is="required")
	 * @ORM\Column(type="string", name="trigger_channel", length=100, nullable=false)
	 */
	private $channel;

	/**
	 * @var string
	 *
	 * @IPubDoctrine\Crud(is="required")
	 * @ORM\Column(type="string", name="trigger_property", length=100, nullable=false)
	 */
	private $property;

	/**
	 * @var Types\ConditionOperatorType
	 *
	 * @IPubDoctrine\Crud(is={"required", "writable"})
	 * @Enum(class=Types\ConditionOperatorType::class)
	 * @ORM\Column(type="string_enum", name="trigger_operator", length=15, nullable=false)
	 */
	private $operator;

	/**
	 * @var string
	 *
	 * @IPubDoctrine\Crud(is={"required", "writable"})
	 * @ORM\Column(type="string", name="trigger_operand", length=100, nullable=false)
	 */
	private $operand;

	/**
	 * @param string $device
	 * @param string $channel
	 * @param string $property
	 * @param Types\ConditionOperatorType $operator
	 * @param string $operand
	 * @param string $name
	 * @param Uuid\UuidInterface|null $id
	 *
	 * @throws Throwable
	 */
	public function __construct(
		string $device,
		string $channel,
		string $property,
		Types\ConditionOperatorType $operator,
		string $operand,
		string $name,
		?Uuid\UuidInterface $id = null
	) {
		parent::__construct($name, $id);

		$this->device = $device;
		$this->channel = $channel;
		$this->property = $property;

		$this->operator = $operator;
		$this->operand = $operand;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDevice(): string
	{
		return $this->device;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getChannel(): string
	{
		return $this->channel;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getProperty(): string
	{
		return $this->property;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setOperator(Types\ConditionOperatorType $operator): void
	{
		$this->operator = $operator;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getOperator(): Types\ConditionOperatorType
	{
		return $this->operator;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setOperand(string $operand): void
	{
		$this->operand = $operand;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getOperand(): string
	{
		return $this->operand;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setNotifications(array $notifications = []): void
	{
		throw new Exceptions\InvalidStateException('Not supported by this type of trigger.');
	}

	/**
	 * {@inheritDoc}
	 */
	public function addNotification(Entities\Notifications\INotification $notification): void
	{
		throw new Exceptions\InvalidStateException('Not supported by this type of trigger.');
	}

	/**
	 * {@inheritDoc}
	 */
	public function getNotifications(): array
	{
		throw new Exceptions\InvalidStateException('Not supported by this type of trigger.');
	}

	/**
	 * {@inheritDoc}
	 */
	public function getNotification(string $id): ?Entities\Notifications\INotification
	{
		throw new Exceptions\InvalidStateException('Not supported by this type of trigger.');
	}

	/**
	 * {@inheritDoc}
	 */
	public function removeNotification(Entities\Notifications\INotification $notification): void
	{
		throw new Exceptions\InvalidStateException('Not supported by this type of trigger.');
	}

}
