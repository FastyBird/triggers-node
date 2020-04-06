<?php declare(strict_types = 1);

use Fig\Http\Message\StatusCodeInterface;

return [
	'createEmail'          => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/notifications',
		file_get_contents(__DIR__ . '/requests/notifications.createEmail.json'),
		StatusCodeInterface::STATUS_CREATED,
		__DIR__ . '/responses/notifications.createEmail.json',
	],
	'createEmailNotUnique' => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/notifications',
		file_get_contents(__DIR__ . '/requests/notifications.createEmail.unique.json'),
		StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
		__DIR__ . '/responses/notifications.createEmail.unique.json',
	],
	'createSms'            => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/notifications',
		file_get_contents(__DIR__ . '/requests/notifications.createSms.json'),
		StatusCodeInterface::STATUS_CREATED,
		__DIR__ . '/responses/notifications.createSms.json',
	],
	'createSmsNotUnique'   => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/notifications',
		file_get_contents(__DIR__ . '/requests/notifications.createSmsNotUnique.unique.json'),
		StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
		__DIR__ . '/responses/notifications.createSmsNotUnique.unique.json',
	],
	'missingRequired'      => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/notifications',
		file_get_contents(__DIR__ . '/requests/notifications.create.missing.required.json'),
		StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
		__DIR__ . '/responses/notifications.create.missing.required.json',
	],
	'invalidType'          => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/notifications',
		file_get_contents(__DIR__ . '/requests/notifications.create.invalidType.json'),
		StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
		__DIR__ . '/responses/notifications.create.invalidType.json',
	],
];
