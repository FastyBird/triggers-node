<?php declare(strict_types = 1);

/**
 * ConditionsV1Controller.php
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
 *
 * @Secured
 * @Secured\User(loggedIn)
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
				StatusCodeInterface::STATUS_NOT_FOUND,
				$this->translator->translate('//node.base.messages.notFound.heading'),
				$this->translator->translate('//node.base.messages.notFound.message')
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
				StatusCodeInterface::STATUS_NOT_FOUND,
				$this->translator->translate('//node.base.messages.notFound.heading'),
				$this->translator->translate('//node.base.messages.notFound.message')
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
	 *
	 * @Secured
	 * @Secured\Role(manager,administrator)
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
						$this->translator->translate('//node.base.messages.invalidType.heading'),
						$this->translator->translate('//node.base.messages.invalidType.message'),
						[
							'pointer' => '/data/type',
						]
					);
				}

				// Commit all changes into database
				$this->getOrmConnection()->commit();

			} catch (DoctrineCrudExceptions\EntityCreationException $ex) {
				throw new NodeJsonApiExceptions\JsonApiErrorException(
					StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
					$this->translator->translate('//node.base.messages.missingAttribute.heading'),
					$this->translator->translate('//node.base.messages.missingAttribute.message'),
					[
						'pointer' => 'data/attributes/' . $ex->getField(),
					]
				);

			} catch (NodeJsonApiExceptions\IJsonApiException $ex) {
				throw $ex;

			} catch (Exceptions\UniqueConditionConstraint $ex) {
				throw new NodeJsonApiExceptions\JsonApiErrorException(
					StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
					$this->translator->translate('messages.propertyNotUnique.heading'),
					$this->translator->translate('messages.propertyNotUnique.message'),
					[
						'pointer' => '/data/relationships/property',
					]
				);

			} catch (Doctrine\DBAL\Exception\UniqueConstraintViolationException $ex) {
				if (preg_match("%PRIMARY'%", $ex->getMessage(), $match) === 1) {
					throw new NodeJsonApiExceptions\JsonApiErrorException(
						StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
						$this->translator->translate('//node.base.messages.uniqueIdentifier.heading'),
						$this->translator->translate('//node.base.messages.uniqueIdentifier.message'),
						[
							'pointer' => '/data/id',
						]
					);

				} elseif (preg_match("%key '(?P<key>.+)_unique'%", $ex->getMessage(), $match) === 1) {
					$columnParts = explode('.', $match['key']);
					$columnKey = end($columnParts);

					if (is_string($columnKey) && Utils\Strings::startsWith($columnKey, 'condition_')) {
						throw new NodeJsonApiExceptions\JsonApiErrorException(
							StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
							$this->translator->translate('//node.base.messages.uniqueAttribute.heading'),
							$this->translator->translate('//node.base.messages.uniqueAttribute.message'),
							[
								'pointer' => '/data/attributes/' . Utils\Strings::substring($columnKey, 10),
							]
						);
					}
				}

				throw new NodeJsonApiExceptions\JsonApiErrorException(
					StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
					$this->translator->translate('//node.base.messages.uniqueAttribute.heading'),
					$this->translator->translate('//node.base.messages.uniqueAttribute.message')
				);

			} catch (Throwable $ex) {
				// Log catched exception
				$this->logger->error('[CONTROLLER] ' . $ex->getMessage(), [
					'exception' => [
						'message' => $ex->getMessage(),
						'code'    => $ex->getCode(),
					],
				]);

				throw new NodeJsonApiExceptions\JsonApiErrorException(
					StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
					$this->translator->translate('//node.base.messages.notCreated.heading'),
					$this->translator->translate('//node.base.messages.notCreated.message')
				);

			} finally {
				// Revert all changes when error occur
				if ($this->getOrmConnection()->isTransactionActive()) {
					$this->getOrmConnection()->rollBack();
				}
			}

			/** @var NodeWebServerHttp\Response $response */
			$response = $response
				->withEntity(NodeWebServerHttp\ScalarEntity::from($condition))
				->withStatus(StatusCodeInterface::STATUS_CREATED);

			return $response;
		}

		throw new NodeJsonApiExceptions\JsonApiErrorException(
			StatusCodeInterface::STATUS_NOT_FOUND,
			$this->translator->translate('//node.base.messages.notFound.heading'),
			$this->translator->translate('//node.base.messages.notFound.message')
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
	 *
	 * @Secured
	 * @Secured\Role(manager,administrator)
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

		$this->validateIdentifier($request, $document);

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
					$this->translator->translate('//node.base.messages.invalidType.heading'),
					$this->translator->translate('//node.base.messages.invalidType.message'),
					[
						'pointer' => '/data/type',
					]
				);
			}

			// Commit all changes into database
			$this->getOrmConnection()->commit();

		} catch (NodeJsonApiExceptions\IJsonApiException $ex) {
			throw $ex;

		} catch (Throwable $ex) {
			// Log catched exception
			$this->logger->error('[CONTROLLER] ' . $ex->getMessage(), [
				'exception' => [
					'message' => $ex->getMessage(),
					'code'    => $ex->getCode(),
				],
			]);

			throw new NodeJsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('//node.base.messages.notUpdated.heading'),
				$this->translator->translate('//node.base.messages.notUpdated.message')
			);

		} finally {
			// Revert all changes when error occur
			if ($this->getOrmConnection()->isTransactionActive()) {
				$this->getOrmConnection()->rollBack();
			}
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
	 *
	 * @Secured
	 * @Secured\Role(manager,administrator)
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

			throw new NodeJsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('//node.base.messages.notUpdated.heading'),
				$this->translator->translate('//node.base.messages.notDeleted.message')
			);

		} finally {
			// Revert all changes when error occur
			if ($this->getOrmConnection()->isTransactionActive()) {
				$this->getOrmConnection()->rollBack();
			}
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

		return parent::readRelationship($request, $response);
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
					$this->translator->translate('//node.base.messages.notFound.heading'),
					$this->translator->translate('//node.base.messages.notFound.message')
				);
			}

		} catch (Uuid\Exception\InvalidUuidStringException $ex) {
			throw new NodeJsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_NOT_FOUND,
				$this->translator->translate('//node.base.messages.notFound.heading'),
				$this->translator->translate('//node.base.messages.notFound.message')
			);
		}

		return $condition;
	}

}
