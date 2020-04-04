<?php declare(strict_types = 1);

/**
 * IField.php
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

use IPub\JsonAPIDocument;

/**
 * Entity field interface
 *
 * @package        FastyBird:TriggersNode!
 * @subpackage     Hydrators
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IField
{

	/**
	 * @return string
	 */
	public function getMappedName(): string;

	/**
	 * @return string
	 */
	public function getFieldName(): string;

	/**
	 * @return bool
	 */
	public function isRequired(): bool;

	/**
	 * @return bool
	 */
	public function isWritable(): bool;

	/**
	 * @param JsonAPIDocument\Objects\IStandardObject<mixed> $attributes
	 *
	 * @return mixed
	 */
	public function getValue(JsonAPIDocument\Objects\IStandardObject $attributes);

}
