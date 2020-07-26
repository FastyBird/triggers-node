<?php declare(strict_types = 1);

/**
 * TriggerHydrator.php
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

use FastyBird\NodeJsonApi\Hydrators as NodeJsonApiHydrators;
use FastyBird\TriggersNode\Schemas;

/**
 * Trigger entity hydrator
 *
 * @package         FastyBird:TriggersNode!
 * @subpackage      Hydrators
 *
 * @author          Adam Kadlec <adam.kadlec@fastybird.com>
 */
abstract class TriggerHydrator extends NodeJsonApiHydrators\Hydrator
{

	/** @var string */
	protected $entityIdentifier = self::IDENTIFIER_KEY;

	/** @var string[] */
	protected $attributes = [
		'name',
		'comment',
		'enabled',
	];

	/** @var string[] */
	protected $relationships = [
		Schemas\Triggers\TriggerSchema::RELATIONSHIPS_ACTIONS,
		Schemas\Triggers\TriggerSchema::RELATIONSHIPS_NOTIFICATIONS,
	];

	/** @var string */
	protected $translationDomain = 'node.triggers';

}
