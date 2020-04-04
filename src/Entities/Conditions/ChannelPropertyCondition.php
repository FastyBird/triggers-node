<?php declare(strict_types = 1);

/**
 * ChannelPropertyCondition.php
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
use FastyBird\TriggersNode\Types;
use Ramsey\Uuid;
use Throwable;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="fb_conditions_channel_property",
 *     options={
 *       "collate"="utf8mb4_general_ci",
 *       "charset"="utf8mb4",
 *       "comment"="Channels properties conditions"
 *     }
 * )
 */
class ChannelPropertyCondition extends PropertyCondition implements IChannelPropertyCondition
{

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", name="condition_channel", length=100, nullable=false)
	 */
	private $channel;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", name="condition_property", length=100, nullable=false)
	 */
	private $property;

	/**
	 * @param string $channel
	 * @param string $property
	 * @param Types\ConditionOperatorType $operator
	 * @param string $operand
	 * @param Entities\Triggers\IAutomaticTrigger $trigger
	 * @param Uuid\UuidInterface|null $id
	 *
	 * @throws Throwable
	 */
	public function __construct(
		string $channel,
		string $property,
		Types\ConditionOperatorType $operator,
		string $operand,
		Entities\Triggers\IAutomaticTrigger $trigger,
		?Uuid\UuidInterface $id = null
	) {
		parent::__construct($operator, $operand, $trigger, $id);

		$this->channel = $channel;
		$this->property = $property;
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

}
