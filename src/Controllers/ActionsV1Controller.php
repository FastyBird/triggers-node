<?php declare(strict_types = 1);

/**
 * ActionsV1Controller.php
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
use FastyBird\NodeWebServer\Exceptions as NodeWebServerExceptions;
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
use Psr\Http\Message;
use Ramsey\Uuid;
use Throwable;

/**
 * Triggers actions controller
 *
 * @package         FastyBird:TriggersModule!
 * @subpackage      Controllers
 *
 * @author          Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class ActionsV1Controller extends BaseV1Controller
{

	use Controllers\Finders\TTriggerFinder;

	/** @var Models\Triggers\ITriggerRepository */
	protected $triggerRepository;

	/** @var Models\Actions\IActionRepository */
	private $actionRepository;

	/** @var Models\Actions\IActionsManager */
	private $actionsManager;

	/** @var Hydrators\Actions\ChannelPropertyActionHydrator */
	private $channelPropertyActionHydrator;

	/** @var string */
	protected $translationDomain = 'node.actions';

	public function __construct(
		Models\Triggers\ITriggerRepository $triggerRepository,
		Models\Actions\IActionRepository $actionRepository,
		Models\Actions\IActionsManager $actionsManager,
		Hydrators\Actions\ChannelPropertyActionHydrator $channelPropertyActionHydrator
	) {
		$this->triggerRepository = $triggerRepository;
		$this->actionRepository = $actionRepository;
		$this->actionsManager = $actionsManager;

		$this->channelPropertyActionHydrator = $channelPropertyActionHydrator;
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param NodeWebServerHttp\Response $response
	 *
	 * @return NodeWebServerHttp\Response
	 *
	 * @throws NodeWebServerExceptions\IJsonApiException
	 */
	public function index(
		Message\ServerRequestInterface $request,
		NodeWebServerHttp\Response $response
	): NodeWebServerHttp\Response {
		// At first, try to load trigger
		$trigger = $this->findTrigger($request->getAttribute(Router\Router::URL_TRIGGER_ID));

		$findQuery = new Queries\FindActionsQuery();
		$findQuery->forTrigger($trigger);

		$rows = $this->actionRepository->getResultSet($findQuery);

		return $response
			->withEntity(NodeWebServerHttp\ScalarEntity::from($rows));
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param NodeWebServerHttp\Response $response
	 *
	 * @return NodeWebServerHttp\Response
	 *
	 * @throws NodeWebServerExceptions\IJsonApiException
	 */
	public function read(
		Message\ServerRequestInterface $request,
		NodeWebServerHttp\Response $response
	): NodeWebServerHttp\Response {
		// At first, try to load trigger
		$trigger = $this->findTrigger($request->getAttribute(Router\Router::URL_TRIGGER_ID));

		// & action
		$action = $this->findAction($request->getAttribute(Router\Router::URL_ITEM_ID), $trigger);

		return $response
			->withEntity(NodeWebServerHttp\ScalarEntity::from($action));
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param NodeWebServerHttp\Response $response
	 *
	 * @return NodeWebServerHttp\Response
	 *
	 * @throws NodeWebServerExceptions\IJsonApiException
	 * @throws Doctrine\DBAL\ConnectionException
	 */
	public function create(
		Message\ServerRequestInterface $request,
		NodeWebServerHttp\Response $response
	): NodeWebServerHttp\Response {
		// At first, try to load trigger
		$trigger = $this->findTrigger($request->getAttribute(Router\Router::URL_TRIGGER_ID));

		$document = $this->createDocument($request);

		try {
			// Start transaction connection to the database
			$this->getOrmConnection()->beginTransaction();

			if ($document->getResource()->getType() === Schemas\Actions\ChannelPropertyActionSchema::SCHEMA_TYPE) {
				$createValues = $this->channelPropertyActionHydrator->hydrate($document->getResource());
				$createValues->offsetSet('trigger', $trigger);

				$action = $this->actionsManager->create($createValues);

			} else {
				throw new NodeWebServerExceptions\JsonApiErrorException(
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
			$this->getOrmConnection()->rollback();

			throw new NodeWebServerExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('//node.base.messages.missingRequired.heading'),
				$this->translator->translate('//node.base.messages.missingRequired.message'),
				[
					'pointer' => 'data/attributes/' . $ex->getField(),
				]
			);

		} catch (NodeWebServerExceptions\IJsonApiException $ex) {
			// Revert all changes when error occur
			$this->getOrmConnection()->rollback();

			throw $ex;

		} catch (Exceptions\UniqueActionConstraint $ex) {
			// Revert all changes when error occur
			$this->getOrmConnection()->rollback();

			throw new NodeWebServerExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('messages.propertyNotUnique.heading'),
				$this->translator->translate('messages.propertyNotUnique.message'),
				[
					'pointer' => '/data/relationships/property',
				]
			);

		} catch (Throwable $ex) {
			// Revert all changes when error occur
			$this->getOrmConnection()->rollback();

			// Log catched exception
			$this->logger->error('[CONTROLLER] ' . $ex->getMessage(), [
				'exception' => [
					'message' => $ex->getMessage(),
					'code'    => $ex->getCode(),
				],
			]);

			throw new NodeWebServerExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('messages.notCreated.heading'),
				$this->translator->translate('messages.notCreated.message')
			);
		}

		/** @var NodeWebServerHttp\Response $response */
		$response = $response
			->withEntity(NodeWebServerHttp\ScalarEntity::from($action))
			->withStatus(StatusCodeInterface::STATUS_CREATED);

		return $response;
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param NodeWebServerHttp\Response $response
	 *
	 * @return NodeWebServerHttp\Response
	 *
	 * @throws NodeWebServerExceptions\IJsonApiException
	 * @throws Doctrine\DBAL\ConnectionException
	 */
	public function update(
		Message\ServerRequestInterface $request,
		NodeWebServerHttp\Response $response
	): NodeWebServerHttp\Response {
		// At first, try to load trigger
		$trigger = $this->findTrigger($request->getAttribute(Router\Router::URL_TRIGGER_ID));

		// & action
		$action = $this->findAction($request->getAttribute(Router\Router::URL_ITEM_ID), $trigger);

		$document = $this->createDocument($request);

		if ($request->getAttribute(Router\Router::URL_ITEM_ID) !== $document->getResource()->getIdentifier()->getId()) {
			throw new NodeWebServerExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_BAD_REQUEST,
				$this->translator->translate('//node.base.messages.invalid.heading'),
				$this->translator->translate('//node.base.messages.invalid.message')
			);
		}

		try {
			// Start transaction connection to the database
			$this->getOrmConnection()->beginTransaction();

			if ($document->getResource()->getType() === Schemas\Actions\ChannelPropertyActionSchema::SCHEMA_TYPE) {
				$action = $this->actionsManager->update(
					$action,
					$this->channelPropertyActionHydrator->hydrate($document->getResource(), $action)
				);

			} else {
				throw new NodeWebServerExceptions\JsonApiErrorException(
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

		} catch (NodeWebServerExceptions\IJsonApiException $ex) {
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

			throw new NodeWebServerExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('messages.notUpdated.heading'),
				$this->translator->translate('messages.notUpdated.message')
			);
		}

		return $response
			->withEntity(NodeWebServerHttp\ScalarEntity::from($action));
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param NodeWebServerHttp\Response $response
	 *
	 * @return NodeWebServerHttp\Response
	 *
	 * @throws NodeWebServerExceptions\IJsonApiException
	 * @throws Doctrine\DBAL\ConnectionException
	 */
	public function delete(
		Message\ServerRequestInterface $request,
		NodeWebServerHttp\Response $response
	): NodeWebServerHttp\Response {
		// At first, try to load trigger
		$trigger = $this->findTrigger($request->getAttribute(Router\Router::URL_TRIGGER_ID));

		// & action
		$action = $this->findAction($request->getAttribute(Router\Router::URL_ITEM_ID), $trigger);

		try {
			// Start transaction connection to the database
			$this->getOrmConnection()->beginTransaction();

			$this->actionsManager->delete($action);

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
			$this->getOrmConnection()->rollback();

			throw new NodeWebServerExceptions\JsonApiErrorException(
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
	 * @throws NodeWebServerExceptions\IJsonApiException
	 */
	public function readRelationship(
		Message\ServerRequestInterface $request,
		NodeWebServerHttp\Response $response
	): NodeWebServerHttp\Response {
		// At first, try to load trigger
		$trigger = $this->findTrigger($request->getAttribute(Router\Router::URL_TRIGGER_ID));

		// & action
		$action = $this->findAction($request->getAttribute(Router\Router::URL_ITEM_ID), $trigger);

		$relationEntity = strtolower($request->getAttribute(Router\Router::RELATION_ENTITY));

		if ($relationEntity === Schemas\Actions\ActionSchema::RELATIONSHIPS_TRIGGER) {
			return $response
				->withEntity(NodeWebServerHttp\ScalarEntity::from($action->getTrigger()));
		}

		$this->throwUnknownRelation($relationEntity);

		return $response;
	}

	/**
	 * @param string $id
	 * @param Entities\Triggers\ITrigger $trigger
	 *
	 * @return Entities\Actions\IAction
	 *
	 * @throws NodeWebServerExceptions\IJsonApiException
	 */
	protected function findAction(
		string $id,
		Entities\Triggers\ITrigger $trigger
	): Entities\Actions\IAction {
		try {
			$findQuery = new Queries\FindActionsQuery();
			$findQuery->byId(Uuid\Uuid::fromString($id));
			$findQuery->forTrigger($trigger);

			$action = $this->actionRepository->findOneBy($findQuery);

			if ($action === null) {
				throw new NodeWebServerExceptions\JsonApiErrorException(
					StatusCodeInterface::STATUS_NOT_FOUND,
					$this->translator->translate('messages.notFound.heading'),
					$this->translator->translate('messages.notFound.message')
				);
			}

		} catch (Uuid\Exception\InvalidUuidStringException $ex) {
			throw new NodeWebServerExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_NOT_FOUND,
				$this->translator->translate('messages.notFound.heading'),
				$this->translator->translate('messages.notFound.message')
			);
		}

		return $action;
	}

}
