<?php declare(strict_types = 1);

use Fig\Http\Message\StatusCodeInterface;

return [
	'readAll'                  => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/notifications',
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/notifications.index.json',
	],
	'readAllPaging'            => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/notifications?page[offset]=1&page[limit]=1',
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/notifications.index.paging.json',
	],
	'readOne'                  => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/notifications/05f28df9-5f19-4923-b3f8-b9090116dadc',
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/notifications.read.json',
	],
	'readOneInclude'           => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/notifications/05f28df9-5f19-4923-b3f8-b9090116dadc?include=trigger',
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/notifications.read.include.json',
	],
	'readOneUnknown'           => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/notifications/69786d15-fd0c-4d9f-9378-33287c2009af',
		StatusCodeInterface::STATUS_NOT_FOUND,
		__DIR__ . '/responses/notifications.notFound.json',
	],
	'readRelationshipsActions' => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/notifications/05f28df9-5f19-4923-b3f8-b9090116dadc/relationships/trigger',
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/notifications.readRelationships.trigger.json',
	],
	'readRelationshipsUnknown' => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/notifications/05f28df9-5f19-4923-b3f8-b9090116dadc/relationships/unknown',
		StatusCodeInterface::STATUS_NOT_FOUND,
		__DIR__ . '/responses/relation.unknown.json',
	],
];