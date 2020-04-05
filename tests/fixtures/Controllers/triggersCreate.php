<?php declare(strict_types = 1);

use Fig\Http\Message\StatusCodeInterface;

return [
	'createManual'          => [
		'/v1/triggers',
		file_get_contents(__DIR__ . '/requests/triggers.createManual.json'),
		StatusCodeInterface::STATUS_CREATED,
		__DIR__ . '/responses/triggers.createManual.json',
	],
	'createChannelProperty' => [
		'/v1/triggers',
		file_get_contents(__DIR__ . '/requests/triggers.createChannelProperty.json'),
		StatusCodeInterface::STATUS_CREATED,
		__DIR__ . '/responses/triggers.createChannelProperty.json',
	],
	'missingRequired'       => [
		'/v1/triggers',
		file_get_contents(__DIR__ . '/requests/triggers.create.missing.required.json'),
		StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
		__DIR__ . '/responses/triggers.create.missing.required.json',
	],
	'invalidType'           => [
		'/v1/triggers',
		file_get_contents(__DIR__ . '/requests/triggers.create.invalidType.json'),
		StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
		__DIR__ . '/responses/triggers.create.invalidType.json',
	],
];
