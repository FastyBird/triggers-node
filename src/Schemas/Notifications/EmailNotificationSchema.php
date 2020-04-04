<?php declare(strict_types = 1);

/**
 * EmailNotificationSchema.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:TriggersNode!
 * @subpackage     Schemas
 * @since          0.1.0
 *
 * @date           04.04.20
 */

namespace FastyBird\TriggersNode\Schemas\Notifications;

use FastyBird\TriggersNode\Entities;
use Neomerx\JsonApi;

/**
 * Trigger email notification entity schema
 *
 * @package         FastyBird:TriggersNode!
 * @subpackage      Schemas
 *
 * @phpstan-extends NotificationSchema<Entities\Notifications\IEmailNotification>
 */
final class EmailNotificationSchema extends NotificationSchema
{

	/**
	 * Define entity schema type string
	 */
	public const SCHEMA_TYPE = 'triggers-node/notification-email';

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return self::SCHEMA_TYPE;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getEntityClass(): string
	{
		return Entities\Notifications\EmailNotification::class;
	}

	/**
	 * @param Entities\Notifications\IEmailNotification $notification
	 * @param JsonApi\Contracts\Schema\ContextInterface $context
	 *
	 * @return iterable<string, mixed>
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getAttributes($notification, JsonApi\Contracts\Schema\ContextInterface $context): iterable
	{
		return array_merge((array) parent::getAttributes($notification, $context), [
			'email' => $notification->getEmail(),
		]);
	}

}
