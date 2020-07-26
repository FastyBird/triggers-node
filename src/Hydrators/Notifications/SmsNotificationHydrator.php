<?php declare(strict_types = 1);

/**
 * SmsNotificationHydrator.php
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

use Contributte\Translation;
use Doctrine\Common;
use FastyBird\NodeJsonApi\Exceptions as NodeJsonApiExceptions;
use FastyBird\TriggersNode\Entities;
use FastyBird\TriggersNode\Models;
use Fig\Http\Message\StatusCodeInterface;
use IPub\JsonAPIDocument;
use IPub\Phone;

/**
 * SMS notification entity hydrator
 *
 * @package         FastyBird:TriggersNode!
 * @subpackage      Hydrators
 *
 * @author          Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class SmsNotificationHydrator extends NotificationHydrator
{

	/** @var string[] */
	protected $attributes = [
		'phone',
	];

	/** @var Phone\Phone */
	private $phone;

	public function __construct(
		Phone\Phone $phone,
		Models\Triggers\ITriggerRepository $triggerRepository,
		Common\Persistence\ManagerRegistry $managerRegistry,
		Translation\Translator $translator
	) {
		parent::__construct($triggerRepository, $managerRegistry, $translator);

		$this->phone = $phone;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getEntityName(): string
	{
		return Entities\Notifications\SmsNotification::class;
	}

	/**
	 * @param JsonAPIDocument\Objects\IStandardObject<mixed> $attributes
	 *
	 * @return Phone\Entities\Phone
	 *
	 * @throws NodeJsonApiExceptions\IJsonApiException
	 * @throws Phone\Exceptions\NoValidCountryException
	 * @throws Phone\Exceptions\NoValidPhoneException
	 * @throws Phone\Exceptions\NoValidTypeException
	 */
	protected function hydratePhoneAttribute(
		JsonAPIDocument\Objects\IStandardObject $attributes
	): Phone\Entities\Phone {
		// Condition operator have to be set
		if (!$attributes->has('phone')) {
			throw new NodeJsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('messages.invalidPhone.heading'),
				$this->translator->translate('messages.invalidPhone.message'),
				[
					'pointer' => '/data/attributes/phone',
				]
			);
		}

		if (!$this->phone->isValid((string) $attributes->get('phone'), 'CZ')) {
			throw new NodeJsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('messages.invalidPhone.heading'),
				$this->translator->translate('messages.invalidPhone.message'),
				[
					'pointer' => '/data/attributes/phone',
				]
			);
		}

		return $this->phone->parse((string) $attributes->get('phone'), 'CZ');
	}

}
