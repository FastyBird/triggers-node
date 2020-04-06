<?php declare(strict_types = 1);

use Fig\Http\Message\StatusCodeInterface;

return [
	'createChannelProperty'          => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/actions',
		file_get_contents(__DIR__ . '/requests/actions.createChannelProperty.json'),
		StatusCodeInterface::STATUS_CREATED,
		__DIR__ . '/responses/actions.createChannelProperty.json',
	],
	'createChannelPropertyNotUnique' => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/actions',
		file_get_contents(__DIR__ . '/requests/actions.createChannelProperty.unique.json'),
		StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
		__DIR__ . '/responses/actions.createChannelProperty.unique.json',
	],
	'missingRequired'                => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/actions',
		file_get_contents(__DIR__ . '/requests/actions.create.missing.required.json'),
		StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
		__DIR__ . '/responses/actions.create.missing.required.json',
	],
	'invalidType'                    => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/actions',
		file_get_contents(__DIR__ . '/requests/actions.create.invalidType.json'),
		StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
		__DIR__ . '/responses/actions.create.invalidType.json',
	],
];
