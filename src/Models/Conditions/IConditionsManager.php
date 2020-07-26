<?php declare(strict_types = 1);

/**
 * IConditionsManager.php
 *
 * @license        More in license.md
 * @copyright      https://fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:TriggersNode!
 * @subpackage     Models
 * @since          0.1.0
 *
 * @date           04.04.20
 */

namespace FastyBird\TriggersNode\Models\Conditions;

use FastyBird\TriggersNode\Entities;
use Nette\Utils;

/**
 * Conditions entities manager interface
 *
 * @package        FastyBird:TriggersNode!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IConditionsManager
{

	/**
	 * @param Utils\ArrayHash $values
	 *
	 * @return Entities\Conditions\ICondition
	 */
	public function create(
		Utils\ArrayHash $values
	): Entities\Conditions\ICondition;

	/**
	 * @param Entities\Conditions\ICondition $entity
	 * @param Utils\ArrayHash $values
	 *
	 * @return Entities\Conditions\ICondition
	 */
	public function update(
		Entities\Conditions\ICondition $entity,
		Utils\ArrayHash $values
	): Entities\Conditions\ICondition;

	/**
	 * @param Entities\Conditions\ICondition $entity
	 *
	 * @return bool
	 */
	public function delete(
		Entities\Conditions\ICondition $entity
	): bool;

}
