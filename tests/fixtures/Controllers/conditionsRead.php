<?php declare(strict_types = 1);

use Fig\Http\Message\StatusCodeInterface;

return [
	'readAll'                  => [
		'/v1/triggers/1b17bcaa-a19e-45f0-98b4-56211cc648ae/conditions',
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/conditions.index.json',
	],
	'readAllPaging'            => [
		'/v1/triggers/1b17bcaa-a19e-45f0-98b4-56211cc648ae/conditions?page[offset]=1&page[limit]=1',
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/conditions.index.paging.json',
	],
	'readOne'                  => [
		'/v1/triggers/1b17bcaa-a19e-45f0-98b4-56211cc648ae/conditions/09c453b3-c55f-4050-8f1c-b50f8d5728c2',
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/conditions.read.json',
	],
	'readOneInclude'           => [
		'/v1/triggers/1b17bcaa-a19e-45f0-98b4-56211cc648ae/conditions/09c453b3-c55f-4050-8f1c-b50f8d5728c2?include=trigger',
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/conditions.read.include.json',
	],
	'readOneUnknown'           => [
		'/v1/triggers/1b17bcaa-a19e-45f0-98b4-56211cc648ae/conditions/69786d15-fd0c-4d9f-9378-33287c2009af',
		StatusCodeInterface::STATUS_NOT_FOUND,
		__DIR__ . '/responses/conditions.notFound.json',
	],
	'readRelationshipsTrigger' => [
		'/v1/triggers/1b17bcaa-a19e-45f0-98b4-56211cc648ae/conditions/09c453b3-c55f-4050-8f1c-b50f8d5728c2/relationships/trigger',
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/conditions.readRelationships.trigger.json',
	],
	'readRelationshipsUnknown' => [
		'/v1/triggers/1b17bcaa-a19e-45f0-98b4-56211cc648ae/conditions/09c453b3-c55f-4050-8f1c-b50f8d5728c2/relationships/unknown',
		StatusCodeInterface::STATUS_NOT_FOUND,
		__DIR__ . '/responses/relation.unknown.json',
	],
	'readAllInvalid'           => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/conditions',
		StatusCodeInterface::STATUS_BAD_REQUEST,
		__DIR__ . '/responses/conditions.index.invalid.json',
	],
	'readInvalid'              => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/conditions/09c453b3-c55f-4050-8f1c-b50f8d5728c2',
		StatusCodeInterface::STATUS_BAD_REQUEST,
		__DIR__ . '/responses/conditions.index.invalid.json',
	],
];
