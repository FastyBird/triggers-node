<?php declare(strict_types = 1);

/**
 * ActionHydrator.php
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

namespace FastyBird\TriggersNode\Hydrators\Actions;

use FastyBird\NodeJsonApi\Hydrators as NodeJsonApiHydrators;
use FastyBird\TriggersNode\Hydrators;
use FastyBird\TriggersNode\Schemas;
use IPub\JsonAPIDocument;

/**
 * Action entity hydrator
 *
 * @package         FastyBird:TriggersNode!
 * @subpackage      Hydrators
 *
 * @author          Adam Kadlec <adam.kadlec@fastybird.com>
 */
abstract class ActionHydrator extends NodeJsonApiHydrators\Hydrator
{

	/** @var string */
	protected $entityIdentifier = self::IDENTIFIER_KEY;

	/** @var string[] */
	protected $relationships = [
		Schemas\Actions\ActionSchema::RELATIONSHIPS_TRIGGER,
	];

	/** @var string */
	protected $translationDomain = 'node.actions';

	/**
	 * @param JsonAPIDocument\Objects\IStandardObject<mixed> $attributes
	 *
	 * @return bool
	 */
	protected function hydrateEnabledAttribute(JsonAPIDocument\Objects\IStandardObject $attributes): bool
	{
		return (bool) $attributes->get('enabled');
	}

}
