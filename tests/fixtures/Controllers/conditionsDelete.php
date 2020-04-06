<?php declare(strict_types = 1);

use Fig\Http\Message\StatusCodeInterface;

return [
	'delete'        => [
		'/v1/triggers/1b17bcaa-a19e-45f0-98b4-56211cc648ae/conditions/09c453b3-c55f-4050-8f1c-b50f8d5728c2',
		StatusCodeInterface::STATUS_NO_CONTENT,
		__DIR__ . '/responses/conditions.delete.json',
	],
	'deleteUnknown' => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/conditions/69786d15-fd0c-4d9f-9378-33287c2009af',
		StatusCodeInterface::STATUS_NOT_FOUND,
		__DIR__ . '/responses/conditions.notFound.json',
	],
];
