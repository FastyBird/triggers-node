<?php declare(strict_types = 1);

use Fig\Http\Message\StatusCodeInterface;

return [
	'update'      => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/actions/4aa84028-d8b7-4128-95b2-295763634aa4',
		file_get_contents(__DIR__ . '/requests/actions.update.json'),
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/actions.update.json',
	],
	'invalidType' => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/actions/4aa84028-d8b7-4128-95b2-295763634aa4',
		file_get_contents(__DIR__ . '/requests/actions.update.invalidType.json'),
		StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
		__DIR__ . '/responses/actions.update.invalidType.json',
	],
	'idMismatch'  => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/actions/4aa84028-d8b7-4128-95b2-295763634aa4',
		file_get_contents(__DIR__ . '/requests/actions.update.idMismatch.json'),
		StatusCodeInterface::STATUS_BAD_REQUEST,
		__DIR__ . '/responses/invalid.identifier.json',
	],
];
