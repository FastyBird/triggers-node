<?php declare(strict_types = 1);

/**
 * TimeCondition.php
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
use DateTimeZone;
use Doctrine\ORM\Mapping as ORM;
use FastyBird\TriggersNode\Entities;
use FastyBird\TriggersNode\Exceptions;
use Ramsey\Uuid;
use Throwable;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="fb_conditions_time",
 *     options={
 *       "collate"="utf8mb4_general_ci",
 *       "charset"="utf8mb4",
 *       "comment"="Time conditions"
 *     }
 * )
 */
class TimeCondition extends Condition implements ITimeCondition
{

	/**
	 * @var DateTimeInterface
	 *
	 * @ORM\Column(type="time", name="condition_time", nullable=false)
	 */
	private $time;

	/**
	 * @var int[]
	 *
	 * @ORM\Column(type="simple_array", name="condition_days", nullable=false)
	 */
	private $days;

	/**
	 * @param DateTimeInterface $time
	 * @param int[] $days
	 * @param Entities\Triggers\IAutomaticTrigger $trigger
	 * @param Uuid\UuidInterface|null $id
	 *
	 * @throws Throwable
	 */
	public function __construct(
		DateTimeInterface $time,
		array $days,
		Entities\Triggers\IAutomaticTrigger $trigger,
		?Uuid\UuidInterface $id = null
	) {
		parent::__construct($trigger, $id);

		if (method_exists($time, 'setTimezone')) {
			$time->setTimezone(new DateTimeZone('UTC'));
		}

		$this->time = $time;

		$this->setDays($days);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTime(): DateTimeInterface
	{
		return $this->time;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setDays(array $days): void
	{
		foreach ($days as $day) {
			if (!in_array($day, [1, 2, 3, 4, 5, 6, 7], true)) {
				throw new Exceptions\InvalidArgumentException('Provided days array is not valid.');
			}
		}

		$this->days = $days;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDays(): array
	{
		$days = [];

		foreach ($this->days as $day) {
			$days[] = (int) $day;
		}

		return $days;
	}

}
