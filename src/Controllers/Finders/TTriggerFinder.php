<?php declare(strict_types = 1);

/**
 * TTriggerFinder.php
 *
 * @license        More in license.md
 * @copyright      https://fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:TriggersNode!
 * @subpackage     Controllers
 * @since          0.1.0
 *
 * @date           04.04.20
 */

namespace FastyBird\TriggersNode\Controllers\Finders;

use FastyBird\NodeJsonApi\Exceptions as NodeJsonApiExceptions;
use FastyBird\TriggersNode\Entities;
use FastyBird\TriggersNode\Models;
use FastyBird\TriggersNode\Queries;
use Fig\Http\Message\StatusCodeInterface;
use Nette\Localization;
use Ramsey\Uuid;

/**
 * @property-read Localization\ITranslator $translator
 * @property-read Models\Triggers\ITriggerRepository $triggerRepository
 */
trait TTriggerFinder
{

	/**
	 * @param string $id
	 *
	 * @return Entities\Triggers\ITrigger
	 *
	 * @throws NodeJsonApiExceptions\IJsonApiException
	 */
	protected function findTrigger(string $id): Entities\Triggers\ITrigger
	{
		try {
			$findQuery = new Queries\FindTriggersQuery();
			$findQuery->byId(Uuid\Uuid::fromString($id));

			$trigger = $this->triggerRepository->findOneBy($findQuery);

			if ($trigger === null) {
				throw new NodeJsonApiExceptions\JsonApiErrorException(
					StatusCodeInterface::STATUS_NOT_FOUND,
					$this->translator->translate('//node.base.messages.triggerNotFound.heading'),
					$this->translator->translate('//node.base.messages.triggerNotFound.message')
				);
			}

		} catch (Uuid\Exception\InvalidUuidStringException $ex) {
			throw new NodeJsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_NOT_FOUND,
				$this->translator->translate('//node.base.messages.triggerNotFound.heading'),
				$this->translator->translate('//node.base.messages.triggerNotFound.message')
			);
		}

		return $trigger;
	}

}
