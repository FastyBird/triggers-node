<?php declare(strict_types = 1);

/**
 * CollectionField.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:TriggersNode!
 * @subpackage     Hydrators
 * @since          0.1.0
 *
 * @date           04.04.20
 */

namespace FastyBird\TriggersNode\Hydrators\Fields;

use FastyBird\TriggersNode\Exceptions;
use IPub\JsonAPIDocument;

/**
 * Entity entities collection field
 *
 * @package        FastyBird:TriggersNode!
 * @subpackage     Hydrators
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class CollectionField extends EntityField
{

	/**
	 * @param JsonAPIDocument\Objects\IStandardObject<mixed> $attributes
	 *
	 * @return void
	 */
	public function getValue(JsonAPIDocument\Objects\IStandardObject $attributes): void
	{
		throw new Exceptions\InvalidStateException(sprintf('Collection field \'%s\' could not be mapped as attribute.', $this->getMappedName()));
	}

}
