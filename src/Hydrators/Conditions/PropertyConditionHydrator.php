<?php declare(strict_types = 1);

/**
 * PropertyConditionHydrator.php
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

namespace FastyBird\TriggersNode\Hydrators\Conditions;

use FastyBird\NodeJsonApi\Exceptions as NodeJsonApiExceptions;
use FastyBird\TriggersNode\Types;
use Fig\Http\Message\StatusCodeInterface;
use IPub\JsonAPIDocument;

/**
 * Property condition entity hydrator
 *
 * @package         FastyBird:TriggersNode!
 * @subpackage      Hydrators
 *
 * @author          Adam Kadlec <adam.kadlec@fastybird.com>
 */
abstract class PropertyConditionHydrator extends ConditionHydrator
{

	/** @var string[] */
	protected $attributes = [
		'device',
		'property',
		'operator',
		'operand',
	];

	/**
	 * @param JsonAPIDocument\Objects\IStandardObject<mixed> $attributes
	 *
	 * @return string
	 *
	 * @throws NodeJsonApiExceptions\IJsonApiException
	 */
	protected function hydrateDeviceAttribute(
		JsonAPIDocument\Objects\IStandardObject $attributes
	): string {
		if (!$attributes->has('device') || $attributes->get('device') === '') {
			throw new NodeJsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('//node.base.messages.missingRequired.heading'),
				$this->translator->translate('//node.base.messages.missingRequired.message'),
				[
					'pointer' => '/data/attributes/device',
				]
			);
		}

		return (string) $attributes->get('device');
	}

	/**
	 * @param JsonAPIDocument\Objects\IStandardObject<mixed> $attributes
	 *
	 * @return string
	 *
	 * @throws NodeJsonApiExceptions\IJsonApiException
	 */
	protected function hydratePropertyAttribute(
		JsonAPIDocument\Objects\IStandardObject $attributes
	): string {
		if (!$attributes->has('property') || $attributes->get('property') === '') {
			throw new NodeJsonApiExceptions\JsonApiErrorException(
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
	 * @throws NodeJsonApiExceptions\IJsonApiException
	 */
	protected function hydrateOperatorAttribute(
		JsonAPIDocument\Objects\IStandardObject $attributes
	): Types\ConditionOperatorType {
		// Condition operator have to be set
		if (!$attributes->has('operator') || $attributes->get('operator') === '') {
			throw new NodeJsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('//node.base.messages.missingRequired.heading'),
				$this->translator->translate('//node.base.messages.missingRequired.message'),
				[
					'pointer' => '/data/attributes/operator',
				]
			);

			// ...and have to be valid value
		} elseif (!Types\ConditionOperatorType::isValidValue($attributes->get('operator'))) {
			throw new NodeJsonApiExceptions\JsonApiErrorException(
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
	 * @throws NodeJsonApiExceptions\IJsonApiException
	 */
	protected function hydrateOperandAttribute(
		JsonAPIDocument\Objects\IStandardObject $attributes
	): string {
		if (!$attributes->has('operand')) {
			throw new NodeJsonApiExceptions\JsonApiErrorException(
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
