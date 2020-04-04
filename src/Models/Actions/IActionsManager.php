<?php declare(strict_types = 1);

/**
 * IActionsManager.php
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

namespace FastyBird\TriggersNode\Models\Actions;

use FastyBird\TriggersNode\Entities;
use Nette\Utils;

/**
 * Actions entities manager interface
 *
 * @package        FastyBird:TriggersNode!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IActionsManager
{

	/**
	 * @param Utils\ArrayHash $values
	 *
	 * @return Entities\Actions\IAction
	 */
	public function create(
		Utils\ArrayHash $values
	): Entities\Actions\IAction;

	/**
	 * @param Entities\Actions\IAction $entity
	 * @param Utils\ArrayHash $values
	 *
	 * @return Entities\Actions\IAction
	 */
	public function update(
		Entities\Actions\IAction $entity,
		Utils\ArrayHash $values
	): Entities\Actions\IAction;

	/**
	 * @param Entities\Actions\IAction $entity
	 *
	 * @return bool
	 */
	public function delete(
		Entities\Actions\IAction $entity
	): bool;

}
