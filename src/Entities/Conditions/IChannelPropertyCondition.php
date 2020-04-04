<?php declare(strict_types = 1);

/**
 * IChannelPropertyCondition.php
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

/**
 * Channel state condition entity interface
 *
 * @package        FastyBird:TriggersNode!
 * @subpackage     Entities
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IChannelPropertyCondition extends IPropertyCondition
{

	/**
	 * @return string
	 */
	public function getChannel(): string;

	/**
	 * @return string
	 */
	public function getProperty(): string;

}
