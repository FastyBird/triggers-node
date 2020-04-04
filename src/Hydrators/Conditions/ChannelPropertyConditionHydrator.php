<?php declare(strict_types = 1);

/**
 * ChannelPropertyConditionHydrator.php
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

namespace FastyBird\TriggersNode\Hydrators\Conditions;

use FastyBird\NodeWebServer\Exceptions as NodeWebServerExceptions;
use FastyBird\TriggersNode\Entities;
use Fig\Http\Message\StatusCodeInterface;
use IPub\JsonAPIDocument;

/**
 * Channel property condition entity hydrator
 *
 * @package         FastyBird:TriggersNode!
 * @subpackage      Hydrators
 *
 * @author          Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class ChannelPropertyConditionHydrator extends PropertyConditionHydrator
{

	/** @var string[] */
	protected $attributes = [
		'channel',
		'property',
		'operator',
		'operand',
	];

	/**
	 * {@inheritDoc}
	 */
	protected function getEntityName(): string
	{
		return Entities\Conditions\ChannelPropertyCondition::class;
	}

	/**
	 * @param JsonAPIDocument\Objects\IStandardObject<mixed> $attributes
	 *
	 * @return string
	 *
	 * @throws NodeWebServerExceptions\IJsonApiException
	 */
	protected function hydrateChannelAttribute(
		JsonAPIDocument\Objects\IStandardObject $attributes
	): string {
		if (!$attributes->has('channel') || $attributes->get('channel') === '') {
			throw new NodeWebServerExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('//node.base.messages.missingRequired.heading'),
				$this->translator->translate('//node.base.messages.missingRequired.message'),
				[
					'pointer' => '/data/attributes/channel',
				]
			);
		}

		return (string) $attributes->get('channel');
	}

}
