<?php declare(strict_types = 1);

/**
 * IPropertyCondition.php
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

namespace FastyBird\TriggersNode\Entities\Conditions;

use FastyBird\TriggersNode\Types;

/**
 * Device or channel property condition entity interface
 *
 * @package        FastyBird:TriggersNode!
 * @subpackage     Entities
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IPropertyCondition extends ICondition
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

}
