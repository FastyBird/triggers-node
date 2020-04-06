<?php declare(strict_types = 1);

/**
 * ITimeCondition.php
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

/**
 * Time condition entity interface
 *
 * @package        FastyBird:TriggersNode!
 * @subpackage     Entities
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface ITimeCondition extends ICondition
{

	/**
	 * @param DateTimeInterface $time
	 *
	 * @return void
	 */
	public function setTime(DateTimeInterface $time): void;

	/**
	 * @return DateTimeInterface
	 */
	public function getTime(): DateTimeInterface;

	/**
	 * @param int[] $days
	 *
	 * @return void
	 */
	public function setDays(array $days): void;

	/**
	 * @return int[]
	 */
	public function getDays(): array;

}
