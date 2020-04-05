<?php declare(strict_types = 1);

use Fig\Http\Message\StatusCodeInterface;

return [
	'update'      => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4',
		file_get_contents(__DIR__ . '/requests/triggers.update.json'),
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/triggers.update.json',
	],
	'invalidType' => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4',
		file_get_contents(__DIR__ . '/requests/triggers.update.invalidType.json'),
		StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
		__DIR__ . '/responses/triggers.update.invalidType.json',
	],
	'idMismatch'  => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4',
		file_get_contents(__DIR__ . '/requests/triggers.update.idMismatch.json'),
		StatusCodeInterface::STATUS_BAD_REQUEST,
		__DIR__ . '/responses/invalid.identifier.json',
	],
];
