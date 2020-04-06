<?php declare(strict_types = 1);

/**
 * IEmailNotification.php
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

/**
 * Email notification entity interface
 *
 * @package        FastyBird:TriggersNode!
 * @subpackage     Entities
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IEmailNotification extends INotification
{

	/**
	 * @param string $email
	 *
	 * @return void
	 */
	public function setEmail(string $email): void;

	/**
	 * @return string
	 */
	public function getEmail(): string;

}
