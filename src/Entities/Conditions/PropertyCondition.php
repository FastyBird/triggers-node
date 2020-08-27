<?php declare(strict_types = 1);

/**
 * PropertyCondition.php
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

use Consistence\Doctrine\Enum\EnumAnnotation as Enum;
use Doctrine\ORM\Mapping as ORM;
use FastyBird\TriggersNode\Entities;
use FastyBird\TriggersNode\Types;
use IPub\DoctrineCrud\Mapping\Annotation as IPubDoctrine;
use Ramsey\Uuid;
use Throwable;

/**
 * @ORM\MappedSuperclass
 */
abstract class PropertyCondition extends Condition implements IPropertyCondition
{

	/**
	 * @var Types\ConditionOperatorType
	 *
	 * @IPubDoctrine\Crud(is={"required", "writable"})
	 * @Enum(class=Types\ConditionOperatorType::class)
	 * @ORM\Column(type="string_enum", name="condition_operator", length=15, nullable=false)
	 */
	protected $operator;

	/**
	 * @var string
	 *
	 * @IPubDoctrine\Crud(is={"required", "writable"})
	 * @ORM\Column(type="string", name="condition_operand", length=20, nullable=false)
	 */
	protected $operand;

	/**
	 * @param Types\ConditionOperatorType $operator
	 * @param string $operand
	 * @param Entities\Triggers\IAutomaticTrigger $trigger
	 * @param Uuid\UuidInterface|null $id
	 *
	 * @throws Throwable
	 */
	public function __construct(
		Types\ConditionOperatorType $operator,
		string $operand,
		Entities\Triggers\IAutomaticTrigger $trigger,
		?Uuid\UuidInterface $id = null
	) {
		parent::__construct($trigger, $id);

		$this->operator = $operator;
		$this->operand = $operand;
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
	public function toArray(): array
	{
		return array_merge(parent::toArray(), [
			'operator' => $this->getOperator()->getValue(),
			'operand'  => $this->getOperand(),
		]);
	}

}
