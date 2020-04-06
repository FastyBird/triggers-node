<?php declare(strict_types = 1);

use Fig\Http\Message\StatusCodeInterface;

return [
	'update'      => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/notifications/05f28df9-5f19-4923-b3f8-b9090116dadc',
		file_get_contents(__DIR__ . '/requests/notifications.update.json'),
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/notifications.update.json',
	],
	'invalidType' => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/notifications/05f28df9-5f19-4923-b3f8-b9090116dadc',
		file_get_contents(__DIR__ . '/requests/notifications.update.invalidType.json'),
		StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
		__DIR__ . '/responses/notifications.update.invalidType.json',
	],
	'idMismatch'  => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/notifications/05f28df9-5f19-4923-b3f8-b9090116dadc',
		file_get_contents(__DIR__ . '/requests/notifications.update.idMismatch.json'),
		StatusCodeInterface::STATUS_BAD_REQUEST,
		__DIR__ . '/responses/invalid.identifier.json',
	],
];
