<?php declare(strict_types = 1);

/**
 * ChannelPropertyActionSchema.php
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

namespace FastyBird\TriggersNode\Schemas\Actions;

use FastyBird\TriggersNode\Entities;
use Neomerx\JsonApi;

/**
 * Trigger channel state action entity schema
 *
 * @package         FastyBird:TriggersNode!
 * @subpackage      Schemas
 *
 * @author          Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @phpstan-extends ActionSchema<Entities\Actions\IChannelPropertyAction>
 */
final class ChannelPropertyActionSchema extends ActionSchema
{

	/**
	 * Define entity schema type string
	 */
	public const SCHEMA_TYPE = 'triggers-node/action-channel-property';

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
		return Entities\Actions\ChannelPropertyAction::class;
	}

	/**
	 * @param Entities\Actions\IChannelPropertyAction $action
	 * @param JsonApi\Contracts\Schema\ContextInterface $context
	 *
	 * @return iterable<string, mixed>
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getAttributes($action, JsonApi\Contracts\Schema\ContextInterface $context): iterable
	{
		return array_merge((array) parent::getAttributes($action, $context), [
			'channel'  => $action->getChannel(),
			'property' => $action->getProperty(),
			'value'    => $action->getValue(),
		]);
	}

}
