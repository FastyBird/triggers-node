<?php declare(strict_types = 1);

use Fig\Http\Message\StatusCodeInterface;

return [
	'createChannelProperty' => [
		'/v1/triggers/1b17bcaa-a19e-45f0-98b4-56211cc648ae/conditions',
		file_get_contents(__DIR__ . '/requests/conditions.createChannelProperty.json'),
		StatusCodeInterface::STATUS_CREATED,
		__DIR__ . '/responses/conditions.createChannelProperty.json',
	],
	'createDeviceProperty'  => [
		'/v1/triggers/1b17bcaa-a19e-45f0-98b4-56211cc648ae/conditions',
		file_get_contents(__DIR__ . '/requests/conditions.createDeviceProperty.json'),
		StatusCodeInterface::STATUS_CREATED,
		__DIR__ . '/responses/conditions.createDeviceProperty.json',
	],
	'createTime'            => [
		'/v1/triggers/1b17bcaa-a19e-45f0-98b4-56211cc648ae/conditions',
		file_get_contents(__DIR__ . '/requests/conditions.createTime.json'),
		StatusCodeInterface::STATUS_CREATED,
		__DIR__ . '/responses/conditions.createTime.json',
	],
	'missingRequired'       => [
		'/v1/triggers/1b17bcaa-a19e-45f0-98b4-56211cc648ae/conditions',
		file_get_contents(__DIR__ . '/requests/conditions.create.missing.required.json'),
		StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
		__DIR__ . '/responses/conditions.create.missing.required.json',
	],
	'invalidType'           => [
		'/v1/triggers/1b17bcaa-a19e-45f0-98b4-56211cc648ae/conditions',
		file_get_contents(__DIR__ . '/requests/conditions.create.invalidType.json'),
		StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
		__DIR__ . '/responses/conditions.create.invalidType.json',
	],
];
