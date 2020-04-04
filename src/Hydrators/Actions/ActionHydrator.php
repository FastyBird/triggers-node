<?php declare(strict_types = 1);

/**
 * ActionHydrator.php
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

namespace FastyBird\TriggersNode\Hydrators\Actions;

use Contributte\Translation;
use Doctrine\Common;
use FastyBird\NodeWebServer\Exceptions as NodeWebServerExceptions;
use FastyBird\TriggersNode\Entities;
use FastyBird\TriggersNode\Hydrators;
use FastyBird\TriggersNode\Models;
use FastyBird\TriggersNode\Queries;
use FastyBird\TriggersNode\Schemas;
use Fig\Http\Message\StatusCodeInterface;
use IPub\JsonAPIDocument;
use Ramsey\Uuid;

/**
 * Action entity hydrator
 *
 * @package         FastyBird:TriggersNode!
 * @subpackage      Hydrators
 *
 * @author          Adam Kadlec <adam.kadlec@fastybird.com>
 */
abstract class ActionHydrator extends Hydrators\Hydrator
{

	/** @var string */
	protected $entityIdentifier = self::IDENTIFIER_KEY;

	/** @var string[] */
	protected $relationships = [
		Schemas\Actions\ActionSchema::RELATIONSHIPS_TRIGGER,
	];

	/** @var string */
	protected $translationDomain = 'node.actions';

	/** @var Models\Triggers\ITriggerRepository */
	protected $triggerRepository;

	public function __construct(
		Models\Triggers\ITriggerRepository $triggerRepository,
		Common\Persistence\ManagerRegistry $managerRegistry,
		Translation\Translator $translator
	) {
		parent::__construct($managerRegistry, $translator);

		$this->triggerRepository = $triggerRepository;
	}

	/**
	 * @param JsonAPIDocument\Objects\IRelationship<mixed> $relationship
	 *
	 * @return Entities\Triggers\ITrigger
	 *
	 * @throws NodeWebServerExceptions\IJsonApiException
	 */
	protected function hydrateTriggerRelationship(
		JsonAPIDocument\Objects\IRelationship $relationship
	): Entities\Triggers\ITrigger {
		if (
			!$relationship->isHasOne()
			|| $relationship->getIdentifier() === null
			|| !Uuid\Uuid::isValid($relationship->getIdentifier()->getId())
		) {
			throw new NodeWebServerExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_NOT_FOUND,
				$this->translator->translate('messages.relationNotFound.heading'),
				$this->translator->translate('messages.relationNotFound.message'),
				[
					'pointer' => '/data/relationships/trigger/data/id',
				]
			);
		}

		$findQuery = new Queries\FindTriggersQuery();
		$findQuery->byId(Uuid\Uuid::fromString($relationship->getIdentifier()->getId()));

		$trigger = $this->triggerRepository->findOneBy($findQuery);

		if ($trigger === null) {
			throw new NodeWebServerExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_NOT_FOUND,
				$this->translator->translate('messages.relationNotFound.heading'),
				$this->translator->translate('messages.relationNotFound.message'),
				[
					'pointer' => '/data/relationships/trigger/data/id',
				]
			);
		}

		return $trigger;
	}

}
