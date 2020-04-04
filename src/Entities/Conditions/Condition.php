<?php declare(strict_types = 1);

/**
 * Condition.php
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

namespace FastyBird\TriggersNode\Entities\Conditions;

use Doctrine\ORM\Mapping as ORM;
use FastyBird\TriggersNode\Entities;
use IPub\DoctrineTimestampable;
use Ramsey\Uuid;
use Throwable;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="fb_conditions",
 *     options={
 *       "collate"="utf8mb4_general_ci",
 *       "charset"="utf8mb4",
 *       "comment"="Triggers conditions"
 *     }
 * )
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="condition_type", type="string", length=20)
 * @ORM\DiscriminatorMap({
 *    "device_property"   = "FastyBird\TriggersNode\Entities\Conditions\DevicePropertyCondition",
 *    "channel_property"  = "FastyBird\TriggersNode\Entities\Conditions\ChannelPropertyCondition",
 *    "date"              = "FastyBird\TriggersNode\Entities\Conditions\DateCondition",
 *    "time"              = "FastyBird\TriggersNode\Entities\Conditions\TimeCondition"
 * })
 * @ORM\MappedSuperclass
 */
abstract class Condition extends Entities\Entity implements ICondition
{

	use DoctrineTimestampable\Entities\TEntityCreated;
	use DoctrineTimestampable\Entities\TEntityUpdated;

	/**
	 * @var Uuid\UuidInterface
	 *
	 * @ORM\Id
	 * @ORM\Column(type="uuid_binary", name="condition_id")
	 * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
	 */
	protected $id;

	/**
	 * @var Entities\Triggers\IAutomaticTrigger
	 *
	 * @IPubDoctrine\Crud(is="required")
	 * @ORM\ManyToOne(targetEntity="FastyBird\TriggersNode\Entities\Triggers\AutomaticTrigger", inversedBy="conditions")
	 * @ORM\JoinColumn(name="trigger_id", referencedColumnName="trigger_id", onDelete="CASCADE")
	 */
	protected $trigger;

	/**
	 * @param Entities\Triggers\IAutomaticTrigger $trigger
	 * @param Uuid\UuidInterface|null $id
	 *
	 * @throws Throwable
	 */
	public function __construct(
		Entities\Triggers\IAutomaticTrigger $trigger,
		?Uuid\UuidInterface $id = null
	) {
		$this->id = $id ?? Uuid\Uuid::uuid4();

		$this->trigger = $trigger;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTrigger(): Entities\Triggers\IAutomaticTrigger
	{
		return $this->trigger;
	}

}
