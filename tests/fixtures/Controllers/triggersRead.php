<?php declare(strict_types = 1);

use Fig\Http\Message\StatusCodeInterface;

return [
	'readAll'                            => [
		'/v1/triggers',
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/triggers.index.json',
	],
	'readAllPaging'                      => [
		'/v1/triggers?page[offset]=1&page[limit]=1',
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/triggers.index.paging.json',
	],
	'readOne'                            => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4',
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/triggers.read.json',
	],
	'readOneInclude'                     => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4?include=actions,notifications',
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/triggers.read.include.json',
	],
	'readOneUnknown'                     => [
		'/v1/triggers/69786d15-fd0c-4d9f-9378-33287c2009af',
		StatusCodeInterface::STATUS_NOT_FOUND,
		__DIR__ . '/responses/triggers.notFound.json',
	],
	'readRelationshipsActions'           => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/relationships/actions',
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/triggers.readRelationships.actions.json',
	],
	'readRelationshipsNotifications'     => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/relationships/notifications',
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/triggers.readRelationships.notifications.json',
	],
	'readRelationshipsConditionsInvalid' => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/relationships/conditions',
		StatusCodeInterface::STATUS_NOT_FOUND,
		__DIR__ . '/responses/triggers.readRelationships.conditionsInvalid.json',
	],
	'readRelationshipsConditions'        => [
		'/v1/triggers/0b48dfbc-fac2-4292-88dc-7981a121602d/relationships/conditions',
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/triggers.readRelationships.conditions.json',
	],
	'readRelationshipsUnknown'           => [
		'/v1/triggers/0b48dfbc-fac2-4292-88dc-7981a121602d/relationships/unknown',
		StatusCodeInterface::STATUS_NOT_FOUND,
		__DIR__ . '/responses/relation.unknown.json',
	],
];
