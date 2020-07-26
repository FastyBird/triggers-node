<?php declare(strict_types = 1);

/**
 * EmailNotificationHydrator.php
 *
 * @license        More in license.md
 * @copyright      https://fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:TriggersNode!
 * @subpackage     Hydrators
 * @since          0.1.0
 *
 * @date           04.04.20
 */

namespace FastyBird\TriggersNode\Hydrators\Notifications;

use FastyBird\NodeJsonApi\Exceptions as NodeJsonApiExceptions;
use FastyBird\TriggersNode\Entities;
use Fig\Http\Message\StatusCodeInterface;
use IPub\JsonAPIDocument;
use Nette\Utils;

/**
 * Email notification entity hydrator
 *
 * @package         FastyBird:TriggersNode!
 * @subpackage      Hydrators
 *
 * @author          Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class EmailNotificationHydrator extends NotificationHydrator
{

	/** @var string[] */
	protected $attributes = [
		'email',
	];

	/**
	 * {@inheritDoc}
	 */
	protected function getEntityName(): string
	{
		return Entities\Notifications\EmailNotification::class;
	}

	/**
	 * @param JsonAPIDocument\Objects\IStandardObject<mixed> $attributes
	 *
	 * @return string
	 *
	 * @throws NodeJsonApiExceptions\IJsonApiException
	 */
	protected function hydrateEmailAttribute(
		JsonAPIDocument\Objects\IStandardObject $attributes
	): string {
		// Condition operator have to be set
		if (!$attributes->has('email')) {
			throw new NodeJsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('messages.invalidEmailAddress.heading'),
				$this->translator->translate('messages.invalidEmailAddress.message'),
				[
					'pointer' => '/data/attributes/email',
				]
			);
		}

		if (!Utils\Validators::isEmail((string) $attributes->get('email'))) {
			throw new NodeJsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('messages.invalidEmailAddress.heading'),
				$this->translator->translate('messages.invalidEmailAddress.message'),
				[
					'pointer' => '/data/attributes/email',
				]
			);
		}

		return (string) $attributes->get('email');
	}

}
