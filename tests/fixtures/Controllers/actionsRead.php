<?php declare(strict_types = 1);

use Fig\Http\Message\StatusCodeInterface;

return [
	'readAll'                  => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/actions',
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/actions.index.json',
	],
	'readAllPaging'            => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/actions?page[offset]=1&page[limit]=1',
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/actions.index.paging.json',
	],
	'readOne'                  => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/actions/4aa84028-d8b7-4128-95b2-295763634aa4',
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/actions.read.json',
	],
	'readOneInclude'           => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/actions/4aa84028-d8b7-4128-95b2-295763634aa4?include=trigger',
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/actions.read.include.json',
	],
	'readOneUnknown'           => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/actions/69786d15-fd0c-4d9f-9378-33287c2009af',
		StatusCodeInterface::STATUS_NOT_FOUND,
		__DIR__ . '/responses/actions.notFound.json',
	],
	'readRelationshipsActions' => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/actions/4aa84028-d8b7-4128-95b2-295763634aa4/relationships/trigger',
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/actions.readRelationships.trigger.json',
	],
	'readRelationshipsUnknown' => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/actions/4aa84028-d8b7-4128-95b2-295763634aa4/relationships/unknown',
		StatusCodeInterface::STATUS_NOT_FOUND,
		__DIR__ . '/responses/relation.unknown.json',
	],
];
