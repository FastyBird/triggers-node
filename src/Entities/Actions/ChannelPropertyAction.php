<?php declare(strict_types = 1);

/**
 * ChannelPropertyAction.php
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

namespace FastyBird\TriggersNode\Entities\Actions;

use Doctrine\ORM\Mapping as ORM;
use FastyBird\TriggersNode\Entities;
use IPub\DoctrineCrud\Mapping\Annotation as IPubDoctrine;
use Ramsey\Uuid;
use Throwable;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="fb_actions_channel_property",
 *     options={
 *       "collate"="utf8mb4_general_ci",
 *       "charset"="utf8mb4",
 *       "comment"="Channels actions"
 *     },
 *     indexes={
 *       @ORM\Index(name="action_channel_idx", columns={"action_channel"}),
 *       @ORM\Index(name="action_property_idx", columns={"action_property"})
 *     }
 * )
 */
class ChannelPropertyAction extends Action implements IChannelPropertyAction
{

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", name="action_channel", length=100, nullable=false)
	 */
	private $channel;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", name="action_property", length=100, nullable=false)
	 */
	private $property;

	/**
	 * @var string
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string", name="action_value", length=100, nullable=false)
	 */
	private $value;

	/**
	 * @param string $channel
	 * @param string $property
	 * @param string $value
	 * @param Entities\Triggers\ITrigger $trigger
	 * @param Uuid\UuidInterface|null $id
	 *
	 * @throws Throwable
	 */
	public function __construct(
		string $channel,
		string $property,
		string $value,
		Entities\Triggers\ITrigger $trigger,
		?Uuid\UuidInterface $id = null
	) {
		parent::__construct($trigger, $id);

		$this->channel = $channel;
		$this->property = $property;

		$this->value = $value;
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
	public function getValue(): string
	{
		return $this->value;
	}

}
