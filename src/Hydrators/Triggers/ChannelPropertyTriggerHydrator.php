<?php declare(strict_types = 1);

/**
 * ChannelPropertyTriggerHydrator.php
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

namespace FastyBird\TriggersNode\Hydrators\Triggers;

use FastyBird\NodeWebServer\Exceptions as NodeWebServerExceptions;
use FastyBird\TriggersNode\Entities;
use FastyBird\TriggersNode\Schemas;
use FastyBird\TriggersNode\Types;
use Fig\Http\Message\StatusCodeInterface;
use IPub\JsonAPIDocument;

/**
 * Channel property trigger entity hydrator
 *
 * @package         FastyBird:TriggersNode!
 * @subpackage      Hydrators
 *
 * @author          Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class ChannelPropertyTriggerHydrator extends TriggerHydrator
{

	/** @var string[] */
	protected $attributes = [
		'name',
		'comment',
		'enabled',
		'operator',
		'operand',
	];

	/** @var string[] */
	protected $relationships = [
		Schemas\Triggers\ChannelPropertyTriggerSchema::RELATIONSHIPS_ACTIONS,
	];

	/**
	 * {@inheritDoc}
	 */
	protected function getEntityName(): string
	{
		return Entities\Triggers\ChannelPropertyTrigger::class;
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

	/**
	 * @param JsonAPIDocument\Objects\IStandardObject<mixed> $attributes
	 *
	 * @return string
	 *
	 * @throws NodeWebServerExceptions\IJsonApiException
	 */
	protected function hydratePropertyAttribute(
		JsonAPIDocument\Objects\IStandardObject $attributes
	): string {
		if (!$attributes->has('property') || $attributes->get('property') === '') {
			throw new NodeWebServerExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('//node.base.messages.missingRequired.heading'),
				$this->translator->translate('//node.base.messages.missingRequired.message'),
				[
					'pointer' => '/data/attributes/property',
				]
			);
		}

		return (string) $attributes->get('property');
	}

	/**
	 * @param JsonAPIDocument\Objects\IStandardObject<mixed> $attributes
	 *
	 * @return Types\ConditionOperatorType
	 *
	 * @throws NodeWebServerExceptions\IJsonApiException
	 */
	protected function hydrateOperatorAttribute(
		JsonAPIDocument\Objects\IStandardObject $attributes
	): Types\ConditionOperatorType {
		// Condition operator have to be set
		if (!$attributes->has('operator') || $attributes->get('operator') === '') {
			throw new NodeWebServerExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('//node.base.messages.missingRequired.heading'),
				$this->translator->translate('//node.base.messages.missingRequired.message'),
				[
					'pointer' => '/data/attributes/operator',
				]
			);

			// ...and have to be valid value
		} elseif (!Types\ConditionOperatorType::isValidValue($attributes->get('operator'))) {
			throw new NodeWebServerExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('messages.invalidOperator.heading'),
				$this->translator->translate('messages.invalidOperator.message'),
				[
					'pointer' => '/data/attributes/operator',
				]
			);
		}

		return Types\ConditionOperatorType::get($attributes->get('operator'));
	}

	/**
	 * @param JsonAPIDocument\Objects\IStandardObject<mixed> $attributes
	 *
	 * @return string
	 *
	 * @throws NodeWebServerExceptions\IJsonApiException
	 */
	protected function hydrateOperandAttribute(
		JsonAPIDocument\Objects\IStandardObject $attributes
	): string {
		if (!$attributes->has('operand')) {
			throw new NodeWebServerExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('//triggers.api.base.messages.missingMandatory.heading'),
				$this->translator->translate('//triggers.api.base.messages.missingMandatory.message'),
				[
					'pointer' => '/data/attributes/operand',
				]
			);
		}

		return (string) $attributes->get('operand');
	}

}
