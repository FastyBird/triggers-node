<?php declare(strict_types = 1);

/**
 * DateCondition.php
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

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use FastyBird\TriggersNode\Entities;
use Ramsey\Uuid;
use Throwable;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="fb_conditions_date",
 *     options={
 *       "collate"="utf8mb4_general_ci",
 *       "charset"="utf8mb4",
 *       "comment"="Date conditions"
 *     }
 * )
 */
class DateCondition extends Condition implements IDateCondition
{

	/**
	 * @var DateTimeInterface
	 *
	 * @ORM\Column(type="datetime", name="condition_date", nullable=false)
	 */
	private $date;

	/**
	 * @param DateTimeInterface $date
	 * @param Entities\Triggers\IAutomaticTrigger $trigger
	 * @param Uuid\UuidInterface|null $id
	 *
	 * @throws Throwable
	 */
	public function __construct(
		DateTimeInterface $date,
		Entities\Triggers\IAutomaticTrigger $trigger,
		?Uuid\UuidInterface $id = null
	) {
		parent::__construct($trigger, $id);

		$this->date = $date;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDate(): DateTimeInterface
	{
		return $this->date;
	}

}
