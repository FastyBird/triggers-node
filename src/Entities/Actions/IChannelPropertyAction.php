<?php declare(strict_types = 1);

/**
 * IChannelPropertyAction.php
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

namespace FastyBird\TriggersNode\Entities\Actions;

/**
 * Channel state action entity interface
 *
 * @package        FastyBird:TriggersNode!
 * @subpackage     Entities
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IChannelPropertyAction extends IAction
{

	/**
	 * @return string
	 */
	public function getDevice(): string;

	/**
	 * @return string
	 */
	public function getChannel(): string;

	/**
	 * @return string
	 */
	public function getProperty(): string;

	/**
	 * @return string
	 */
	public function getValue(): string;

}
