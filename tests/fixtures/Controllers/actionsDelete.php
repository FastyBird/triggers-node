<?php declare(strict_types = 1);

use Fig\Http\Message\StatusCodeInterface;

return [
	'delete'        => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/actions/4aa84028-d8b7-4128-95b2-295763634aa4',
		StatusCodeInterface::STATUS_NO_CONTENT,
		__DIR__ . '/responses/actions.delete.json',
	],
	'deleteUnknown' => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/actions/69786d15-fd0c-4d9f-9378-33287c2009af',
		StatusCodeInterface::STATUS_NOT_FOUND,
		__DIR__ . '/responses/actions.notFound.json',
	],
];