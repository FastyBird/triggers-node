<?php declare(strict_types = 1);

/**
 * TimeConditionHydrator.php
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

use DateTime;
use DateTimeInterface;
use FastyBird\NodeJsonApi\Exceptions as NodeJsonApiExceptions;
use FastyBird\TriggersNode\Entities;
use Fig\Http\Message\StatusCodeInterface;
use IPub\JsonAPIDocument;
use Nette\Utils;

/**
 * Time condition entity hydrator
 *
 * @package         FastyBird:TriggersNode!
 * @subpackage      Hydrators
 *
 * @author          Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class TimeConditionHydrator extends ConditionHydrator
{

	/** @var string[] */
	protected $attributes = [
		'time',
		'days',
	];

	/**
	 * {@inheritDoc}
	 */
	protected function getEntityName(): string
	{
		return Entities\Conditions\TimeCondition::class;
	}

	/**
	 * @param JsonAPIDocument\Objects\IStandardObject<mixed> $attributes
	 *
	 * @return DateTimeInterface
	 *
	 * @throws NodeJsonApiExceptions\IJsonApiException
	 */
	protected function hydrateTimeAttribute(
		JsonAPIDocument\Objects\IStandardObject $attributes
	): DateTimeInterface {
		// Condition time have to be set
		if (!$attributes->has('time')) {
			throw new NodeJsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('//node.base.messages.missingRequired.heading'),
				$this->translator->translate('//node.base.messages.missingRequired.message'),
				[
					'pointer' => '/data/attributes/time',
				]
			);
		}

		$date = Utils\DateTime::createFromFormat(DateTime::ATOM, (string) $attributes->get('time'));

		if (!$date instanceof DateTimeInterface || $date->format(DateTime::ATOM) !== $attributes->get('time')) {
			throw new NodeJsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('messages.invalidTime.heading'),
				$this->translator->translate('messages.invalidTime.message'),
				[
					'pointer' => '/data/attributes/time',
				]
			);
		}

		return $date;
	}

	/**
	 * @param JsonAPIDocument\Objects\IStandardObject<mixed> $attributes
	 *
	 * @return int[]
	 *
	 * @throws NodeJsonApiExceptions\IJsonApiException
	 */
	protected function hydrateDaysAttribute(
		JsonAPIDocument\Objects\IStandardObject $attributes
	): array {
		// Condition days have to be set
		if (!$attributes->has('days')) {
			throw new NodeJsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('//triggers.api.base.messages.missingMandatory.heading'),
				$this->translator->translate('//triggers.api.base.messages.missingMandatory.message'),
				[
					'pointer' => '/data/attributes/days',
				]
			);

		} elseif (!is_array($attributes->get('days'))) {
			throw new NodeJsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('messages.invalidDays.heading'),
				$this->translator->translate('messages.invalidDays.message'),
				[
					'pointer' => '/data/attributes/days',
				]
			);
		}

		$days = [];

		foreach ($attributes->get('days') as $day) {
			if (in_array($day, [1, 2, 3, 4, 5, 6, 7], true)) {
				$days[] = $day;
			}
		}

		return $days;
	}

}
