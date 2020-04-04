<?php declare(strict_types = 1);

/**
 * IChannelPropertyTrigger.php
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

namespace FastyBird\TriggersNode\Entities\Triggers;

use FastyBird\TriggersNode\Types;

/**
 * Machine device channel trigger entity interface
 *
 * @package        FastyBird:TriggersNode!
 * @subpackage     Entities
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IChannelPropertyTrigger extends ITrigger
{

	/**
	 * @param Types\ConditionOperatorType $operator
	 *
	 * @return void
	 */
	public function setOperator(Types\ConditionOperatorType $operator): void;

	/**
	 * @return Types\ConditionOperatorType
	 */
	public function getOperator(): Types\ConditionOperatorType;

	/**
	 * @param string $operand
	 *
	 * @return void
	 */
	public function setOperand(string $operand): void;

	/**
	 * @return string
	 */
	public function getOperand(): string;

	/**
	 * @return string
	 */
	public function getChannel(): string;

	/**
	 * @return string
	 */
	public function getProperty(): string;

}
