<?php declare(strict_types = 1);

/**
 * ISmsNotification.php
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

namespace FastyBird\TriggersNode\Entities\Notifications;

use IPub\Phone;

/**
 * SMS notification entity interface
 *
 * @package        FastyBird:TriggersNode!
 * @subpackage     Entities
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface ISmsNotification extends INotification
{

	/**
	 * @param Phone\Entities\Phone $phone
	 *
	 * @return void
	 */
	public function setPhone(Phone\Entities\Phone $phone): void;

	/**
	 * @return Phone\Entities\Phone
	 */
	public function getPhone(): Phone\Entities\Phone;

}
