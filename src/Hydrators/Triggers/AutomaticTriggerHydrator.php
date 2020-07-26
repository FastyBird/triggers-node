<?php declare(strict_types = 1);

/**
 * AutomaticTriggerHydrator.php
 *
 * @license        More in license.md
 * @copyright      https://fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:TriggersNode!
 * @subpackage     Hydrators
 * @since          0.1.0
 *
 * @date           04.04.20
 */

namespace FastyBird\TriggersNode\Hydrators\Triggers;

use FastyBird\TriggersNode\Entities;
use FastyBird\TriggersNode\Schemas;

/**
 * Automatic trigger entity hydrator
 *
 * @package         FastyBird:TriggersNode!
 * @subpackage      Hydrators
 *
 * @author          Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class AutomaticTriggerHydrator extends TriggerHydrator
{

	/** @var string[] */
	protected $relationships = [
		Schemas\Triggers\AutomaticTriggerSchema::RELATIONSHIPS_CONDITIONS,
		Schemas\Triggers\AutomaticTriggerSchema::RELATIONSHIPS_ACTIONS,
		Schemas\Triggers\AutomaticTriggerSchema::RELATIONSHIPS_NOTIFICATIONS,
	];

	/**
	 * {@inheritDoc}
	 */
	protected function getEntityName(): string
	{
		return Entities\Triggers\AutomaticTrigger::class;
	}

}
