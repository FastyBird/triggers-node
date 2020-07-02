<?php declare(strict_types = 1);

/**
 * ConditionsV1Controller.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:TriggersNode!
 * @subpackage     Controllers
 * @since          0.1.0
 *
 * @date           04.04.20
 */

namespace FastyBird\TriggersNode\Controllers;

use Doctrine;
use FastyBird\NodeJsonApi\Exceptions as NodeJsonApiExceptions;
use FastyBird\NodeWebServer\Http as NodeWebServerHttp;
use FastyBird\TriggersNode\Controllers;
use FastyBird\TriggersNode\Entities;
use FastyBird\TriggersNode\Exceptions;
use FastyBird\TriggersNode\Hydrators;
use FastyBird\TriggersNode\Models;
use FastyBird\TriggersNode\Queries;
use FastyBird\TriggersNode\Router;
use FastyBird\TriggersNode\Schemas;
use Fig\Http\Message\StatusCodeInterface;
use IPub\DoctrineCrud\Exceptions as DoctrineCrudExceptions;
use Nette\Utils;
use Psr\Http\Message;
use Ramsey\Uuid;
use Throwable;

/**
 * Triggers conditions controller
 *
 * @package         FastyBird:TriggersModule!
 * @subpackage      Controllers
 *
 * @author          Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class ConditionsV1Controller extends BaseV1Controller
{

	use Controllers\Finders\TTriggerFinder;

	/** @var Models\Triggers\ITriggerRepository */
	protected $triggerRepository;

	/** @var Models\Conditions\IConditionRepository */
	private $conditionRepository;

	/** @var Models\Conditions\IConditionsManager */
	private $conditionsManager;

	/** @var Hydrators\Conditions\DevicePropertyConditionHydrator */
	private $devicePropertyConditionHydrator;

	/** @var Hydrators\Conditions\ChannelPropertyConditionHydrator */
	private $channelPropertyConditionHydrator;

	/** @var Hydrators\Conditions\TimeConditionHydrator */
	private $timeConditionHydrator;

	/** @var string */
	protected $translationDomain = 'node.conditions';

	public function __construct(
		Models\Triggers\ITriggerRepository $triggerRepository,
		Models\Conditions\IConditionRepository $conditionRepository,
		Models\Conditions\IConditionsManager $conditionsManager,
		Hydrators\Conditions\DevicePropertyConditionHydrator $devicePropertyConditionHydrator,
		Hydrators\Conditions\ChannelPropertyConditionHydrator $channelPropertyConditionHydrator,
		Hydrators\Conditions\TimeConditionHydrator $timeConditionHydrator
	) {
		$this->triggerRepository = $triggerRepository;
		$this->conditionRepository = $conditionRepository;
		$this->conditionsManager = $conditionsManager;

		$this->devicePropertyConditionHydrator = $devicePropertyConditionHydrator;
		$this->channelPropertyConditionHydrator = $channelPropertyConditionHydrator;
		$this->timeConditionHydrator = $timeConditionHydrator;
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param NodeWebServerHttp\Response $response
	 *
	 * @return NodeWebServerHttp\Response
	 *
	 * @throws NodeJsonApiExceptions\IJsonApiException
	 */
	public function index(
		Message\ServerRequestInterface $request,
		NodeWebServerHttp\Response $response
	): NodeWebServerHttp\Response {
		// At first, try to load trigger
		$trigger = $this->findTrigger($request->getAttribute(Router\Router::URL_TRIGGER_ID));

		if (!$trigger instanceof Entities\Triggers\IAutomaticTrigger) {
			throw new NodeJsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_BAD_REQUEST,
				$this->translator->translate('//node.base.messages.invalidTriggerType.heading'),
				$this->translator->translate('//node.base.messages.invalidTriggerType.message')
			);
		}

		$findQuery = new Queries\FindConditionsQuery();
		$findQuery->forTrigger($trigger);

		$rows = $this->conditionRepository->getResultSet($findQuery);

		return $response
			->withEntity(NodeWebServerHttp\ScalarEntity::from($rows));
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param NodeWebServerHttp\Response $response
	 *
	 * @return NodeWebServerHttp\Response
	 *
	 * @throws NodeJsonApiExceptions\IJsonApiException
	 */
	public function read(
		Message\ServerRequestInterface $request,
		NodeWebServerHttp\Response $response
	): NodeWebServerHttp\Response {
		// At first, try to load trigger
		$trigger = $this->findTrigger($request->getAttribute(Router\Router::URL_TRIGGER_ID));

		if (!$trigger instanceof Entities\Triggers\IAutomaticTrigger) {
			throw new NodeJsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_BAD_REQUEST,
				$this->translator->translate('//node.base.messages.invalidTriggerType.heading'),
				$this->translator->translate('//node.base.messages.invalidTriggerType.message')
			);
		}

		// & condition
		$condition = $this->findCondition($request->getAttribute(Router\Router::URL_ITEM_ID), $trigger);

		return $response
			->withEntity(NodeWebServerHttp\ScalarEntity::from($condition));
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param NodeWebServerHttp\Response $response
	 *
	 * @return NodeWebServerHttp\Response
	 *
	 * @throws NodeJsonApiExceptions\IJsonApiException
	 * @throws Doctrine\DBAL\ConnectionException
	 */
	public function create(
		Message\ServerRequestInterface $request,
		NodeWebServerHttp\Response $response
	): NodeWebServerHttp\Response {
		// At first, try to load trigger
		$trigger = $this->findTrigger($request->getAttribute(Router\Router::URL_TRIGGER_ID));

		if ($trigger instanceof Entities\Triggers\IAutomaticTrigger) {
			$document = $this->createDocument($request);

			try {
				// Start transaction connection to the database
				$this->getOrmConnection()->beginTransaction();

				if ($document->getResource()->getType() === Schemas\Conditions\DevicePropertyConditionSchema::SCHEMA_TYPE) {
					$condition = $this->conditionsManager->create($this->devicePropertyConditionHydrator->hydrate($document));

				} elseif ($document->getResource()->getType() === Schemas\Conditions\ChannelPropertyConditionSchema::SCHEMA_TYPE) {
					$condition = $this->conditionsManager->create($this->channelPropertyConditionHydrator->hydrate($document));

				} elseif ($document->getResource()->getType() === Schemas\Conditions\TimeConditionSchema::SCHEMA_TYPE) {
					$condition = $this->conditionsManager->create($this->timeConditionHydrator->hydrate($document));

				} else {
					throw new NodeJsonApiExceptions\JsonApiErrorException(
						StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
						$this->translator->translate('messages.invalidType.heading'),
						$this->translator->translate('messages.invalidType.message'),
						[
							'pointer' => '/data/type',
						]
					);
				}

				// Commit all changes into database
				$this->getOrmConnection()->commit();

			} catch (DoctrineCrudExceptions\EntityCreationException $ex) {
				// Revert all changes when error occur
				$this->getOrmConnection()->rollBack();

				throw new NodeJsonApiExceptions\JsonApiErrorException(
					StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
					$this->translator->translate('//node.base.messages.missingRequired.heading'),
					$this->translator->translate('//node.base.messages.missingRequired.message'),
					[
						'pointer' => 'data/attributes/' . $ex->getField(),
					]
				);

			} catch (NodeJsonApiExceptions\IJsonApiException $ex) {
				// Revert all changes when error occur
				$this->getOrmConnection()->rollBack();

				throw $ex;

			} catch (Exceptions\UniqueConditionConstraint $ex) {
				// Revert all changes when error occur
				$this->getOrmConnection()->rollBack();

				throw new NodeJsonApiExceptions\JsonApiErrorException(
					StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
					$this->translator->translate('messages.propertyNotUnique.heading'),
					$this->translator->translate('messages.propertyNotUnique.message'),
					[
						'pointer' => '/data/relationships/property',
					]
				);

			} catch (Doctrine\DBAL\Exception\UniqueConstraintViolationException $ex) {
				// Revert all changes when error occur
				$this->getOrmConnection()->rollBack();

				if (preg_match("%key '(?P<key>.+)_unique'%", $ex->getMessage(), $match) !== false) {
					if (Utils\Strings::startsWith($match['key'], 'device_')) {
						throw new NodeJsonApiExceptions\JsonApiErrorException(
							StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
							$this->translator->translate('//node.base.messages.uniqueConstraint.heading'),
							$this->translator->translate('//node.base.messages.uniqueConstraint.message'),
							[
								'pointer' => '/data/attributes/' . Utils\Strings::substring($match['key'], 7),
							]
						);
					}
				}

				throw new NodeJsonApiExceptions\JsonApiErrorException(
					StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
					$this->translator->translate('//node.base.messages.uniqueConstraint.heading'),
					$this->translator->translate('//node.base.messages.uniqueConstraint.message')
				);

			} catch (Throwable $ex) {
				// Revert all changes when error occur
				$this->getOrmConnection()->rollBack();

				// Log catched exception
				$this->logger->error('[CONTROLLER] ' . $ex->getMessage(), [
					'exception' => [
						'message' => $ex->getMessage(),
						'code'    => $ex->getCode(),
					],
				]);

				throw new NodeJsonApiExceptions\JsonApiErrorException(
					StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
					$this->translator->translate('messages.notCreated.heading'),
					$this->translator->translate('messages.notCreated.message')
				);
			}

			/** @var NodeWebServerHttp\Response $response */
			$response = $response
				->withEntity(NodeWebServerHttp\ScalarEntity::from($condition))
				->withStatus(StatusCodeInterface::STATUS_CREATED);

			return $response;
		}

		throw new NodeJsonApiExceptions\JsonApiErrorException(
			StatusCodeInterface::STATUS_BAD_REQUEST,
			$this->translator->translate('messages.invalidTrigger.heading'),
			$this->translator->translate('messages.invalidTrigger.message')
		);
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param NodeWebServerHttp\Response $response
	 *
	 * @return NodeWebServerHttp\Response
	 *
	 * @throws NodeJsonApiExceptions\IJsonApiException
	 * @throws Doctrine\DBAL\ConnectionException
	 */
	public function update(
		Message\ServerRequestInterface $request,
		NodeWebServerHttp\Response $response
	): NodeWebServerHttp\Response {
		// At first, try to load trigger
		$trigger = $this->findTrigger($request->getAttribute(Router\Router::URL_TRIGGER_ID));

		// & condition
		$condition = $this->findCondition($request->getAttribute(Router\Router::URL_ITEM_ID), $trigger);

		$document = $this->createDocument($request);

		if ($request->getAttribute(Router\Router::URL_ITEM_ID) !== $document->getResource()->getIdentifier()->getId()) {
			throw new NodeJsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_BAD_REQUEST,
				$this->translator->translate('//node.base.messages.invalid.heading'),
				$this->translator->translate('//node.base.messages.invalid.message')
			);
		}

		try {
			// Start transaction connection to the database
			$this->getOrmConnection()->beginTransaction();

			if ($document->getResource()->getType() === Schemas\Conditions\DevicePropertyConditionSchema::SCHEMA_TYPE) {
				$condition = $this->conditionsManager->update(
					$condition,
					$this->devicePropertyConditionHydrator->hydrate($document, $condition)
				);

			} elseif ($document->getResource()->getType() === Schemas\Conditions\ChannelPropertyConditionSchema::SCHEMA_TYPE) {
				$condition = $this->conditionsManager->update(
					$condition,
					$this->channelPropertyConditionHydrator->hydrate($document, $condition)
				);

			} elseif ($document->getResource()->getType() === Schemas\Conditions\TimeConditionSchema::SCHEMA_TYPE) {
				$condition = $this->conditionsManager->update(
					$condition,
					$this->timeConditionHydrator->hydrate($document, $condition)
				);

			} else {
				throw new NodeJsonApiExceptions\JsonApiErrorException(
					StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
					$this->translator->translate('messages.invalidType.heading'),
					$this->translator->translate('messages.invalidType.message'),
					[
						'pointer' => '/data/type',
					]
				);
			}

			// Commit all changes into database
			$this->getOrmConnection()->commit();

		} catch (NodeJsonApiExceptions\IJsonApiException $ex) {
			// Revert all changes when error occur
			$this->getOrmConnection()->rollBack();

			throw $ex;

		} catch (Throwable $ex) {
			// Revert all changes when error occur
			$this->getOrmConnection()->rollBack();

			// Log catched exception
			$this->logger->error('[CONTROLLER] ' . $ex->getMessage(), [
				'exception' => [
					'message' => $ex->getMessage(),
					'code'    => $ex->getCode(),
				],
			]);

			throw new NodeJsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('messages.notUpdated.heading'),
				$this->translator->translate('messages.notUpdated.message')
			);
		}

		return $response
			->withEntity(NodeWebServerHttp\ScalarEntity::from($condition));
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param NodeWebServerHttp\Response $response
	 *
	 * @return NodeWebServerHttp\Response
	 *
	 * @throws NodeJsonApiExceptions\IJsonApiException
	 * @throws Doctrine\DBAL\ConnectionException
	 */
	public function delete(
		Message\ServerRequestInterface $request,
		NodeWebServerHttp\Response $response
	): NodeWebServerHttp\Response {
		// At first, try to load trigger
		$trigger = $this->findTrigger($request->getAttribute(Router\Router::URL_TRIGGER_ID));

		// & condition
		$condition = $this->findCondition($request->getAttribute(Router\Router::URL_ITEM_ID), $trigger);

		try {
			// Start transaction connection to the database
			$this->getOrmConnection()->beginTransaction();

			$this->conditionsManager->delete($condition);

			// Commit all changes into database
			$this->getOrmConnection()->commit();

		} catch (Throwable $ex) {
			// Log catched exception
			$this->logger->error('[CONTROLLER] ' . $ex->getMessage(), [
				'exception' => [
					'message' => $ex->getMessage(),
					'code'    => $ex->getCode(),
				],
			]);

			// Revert all changes when error occur
			$this->getOrmConnection()->rollBack();

			throw new NodeJsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('messages.notDeleted.heading'),
				$this->translator->translate('messages.notDeleted.message')
			);
		}

		/** @var NodeWebServerHttp\Response $response */
		$response = $response->withStatus(StatusCodeInterface::STATUS_NO_CONTENT);

		return $response;
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param NodeWebServerHttp\Response $response
	 *
	 * @return NodeWebServerHttp\Response
	 *
	 * @throws NodeJsonApiExceptions\IJsonApiException
	 */
	public function readRelationship(
		Message\ServerRequestInterface $request,
		NodeWebServerHttp\Response $response
	): NodeWebServerHttp\Response {
		// At first, try to load trigger
		$trigger = $this->findTrigger($request->getAttribute(Router\Router::URL_TRIGGER_ID));

		// & condition
		$condition = $this->findCondition($request->getAttribute(Router\Router::URL_ITEM_ID), $trigger);

		$relationEntity = strtolower($request->getAttribute(Router\Router::RELATION_ENTITY));

		if ($relationEntity === Schemas\Conditions\ConditionSchema::RELATIONSHIPS_TRIGGER) {
			return $response
				->withEntity(NodeWebServerHttp\ScalarEntity::from($condition->getTrigger()));
		}

		$this->throwUnknownRelation($relationEntity);

		return $response;
	}

	/**
	 * @param string $id
	 * @param Entities\Triggers\ITrigger $trigger
	 *
	 * @return Entities\Conditions\ICondition
	 *
	 * @throws NodeJsonApiExceptions\IJsonApiException
	 */
	protected function findCondition(
		string $id,
		Entities\Triggers\ITrigger $trigger
	): Entities\Conditions\ICondition {
		try {
			$findQuery = new Queries\FindConditionsQuery();
			$findQuery->byId(Uuid\Uuid::fromString($id));
			$findQuery->forTrigger($trigger);

			$condition = $this->conditionRepository->findOneBy($findQuery);

			if ($condition === null) {
				throw new NodeJsonApiExceptions\JsonApiErrorException(
					StatusCodeInterface::STATUS_NOT_FOUND,
					$this->translator->translate('messages.notFound.heading'),
					$this->translator->translate('messages.notFound.message')
				);
			}

		} catch (Uuid\Exception\InvalidUuidStringException $ex) {
			throw new NodeJsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_NOT_FOUND,
				$this->translator->translate('messages.notFound.heading'),
				$this->translator->translate('messages.notFound.message')
			);
		}

		return $condition;
	}

}
