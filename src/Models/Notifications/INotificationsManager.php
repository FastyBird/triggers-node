<?php declare(strict_types = 1);

/**
 * INotificationsManager.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:TriggersNode!
 * @subpackage     Models
 * @since          0.1.0
 *
 * @date           04.04.20
 */

namespace FastyBird\TriggersNode\Models\Notifications;

use FastyBird\TriggersNode\Entities;
use Nette\Utils;

/**
 * Notifications entities manager interface
 *
 * @package        FastyBird:TriggersNode!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface INotificationsManager
{

	/**
	 * @param Utils\ArrayHash $values
	 *
	 * @return Entities\Notifications\INotification
	 */
	public function create(
		Utils\ArrayHash $values
	): Entities\Notifications\INotification;

	/**
	 * @param Entities\Notifications\INotification $entity
	 * @param Utils\ArrayHash $values
	 *
	 * @return Entities\Notifications\INotification
	 */
	public function update(
		Entities\Notifications\INotification $entity,
		Utils\ArrayHash $values
	): Entities\Notifications\INotification;

	/**
	 * @param Entities\Notifications\INotification $entity
	 *
	 * @return bool
	 */
	public function delete(
		Entities\Notifications\INotification $entity
	): bool;

}
