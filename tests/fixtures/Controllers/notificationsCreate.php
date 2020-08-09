<?php declare(strict_types = 1);

use Fig\Http\Message\StatusCodeInterface;

const VALID_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJqdGkiOiI5YWY1NjI0Mi01ZDg3LTQzNjQtYmIxZS1kOWZjODI4NmIzZmYiLCJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbm9kZSIsImlhdCI6MTU4NTc0MjQwMCwiZXhwIjoxNTg1NzQ5NjAwLCJ1c2VyIjoiNWU3OWVmYmYtYmQwZC01YjdjLTQ2ZWYtYmZiZGVmYmZiZDM0Iiwicm9sZXMiOlsiYWRtaW5pc3RyYXRvciJdfQ.Lb-zUa9DL7swdVSEuPTqaR9FvLgKwuEtrhxiJFWjhU8';
const EXPIRED_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJqdGkiOiI3MjFlMTAyNS04Zjc4LTQzOGQtODIwZi0wZDQ2OWEzNzk1NWQiLCJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbm9kZSIsImlhdCI6MTU3Nzg4MDAwMCwiZXhwIjoxNTc3OTAxNjAwLCJ1c2VyIjoiNTI1ZDZhMDktN2MwNi00NmQyLWFmZmEtNzA5YmIxODM3MDdlIiwicm9sZXMiOlsiYWRtaW5pc3RyYXRvciJdfQ.F9veOiNfcqQVxpbMF7OY5j1AcPLpPQb8dEIZbrBmh24';
const INVALID_TOKEN = 'eyJqdGkiOiI5YWY1NjI0Mi01ZDg3LTQzNjQtYmIxZS1kOWZjODI4NmIzZmYiLCJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbm9kZSIsImlhdCI6MTU4NTc0MjQwMCwiZXhwIjoxNTg1NzQ5NjAwLCJ1c2VyIjoiNWU3OWVmYmYtYmQwZC01YjdjLTQ2ZWYtYmZiZGVmYmZiZDM0Iiwicm9sZXMiOlsiYWRtaW5pc3RyYXRvciJdfQ.Lb-zUa9DL7swdVSEuPTqaR9FvLgKwuEtrhxiJFWjhU8';
const VALID_TOKEN_USER = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJqdGkiOiI3YzVkNzdhZC1kOTNlLTRjMmMtOThlNS05ZTFhZmM0NDQ2MTUiLCJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbm9kZSIsImlhdCI6MTU4NTc0MjQwMCwiZXhwIjoxNTg1NzQ5NjAwLCJ1c2VyIjoiZWZiZmJkZWYtYmZiZC02OGVmLWJmYmQtNzcwYjQwZWZiZmJkIiwicm9sZXMiOlsidXNlciJdfQ.cbatWCuGX-K8XbF9MMN7DqxV9hriWmUSGcDGGmnxXX0';

return [
	'createEmail'          => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/notifications',
		'Bearer ' . VALID_TOKEN,
		file_get_contents(__DIR__ . '/requests/notifications.createEmail.json'),
		StatusCodeInterface::STATUS_CREATED,
		__DIR__ . '/responses/notifications.createEmail.json',
	],
	'createEmailNotUnique' => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/notifications',
		'Bearer ' . VALID_TOKEN,
		file_get_contents(__DIR__ . '/requests/notifications.createEmail.unique.json'),
		StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
		__DIR__ . '/responses/notifications.createEmail.unique.json',
	],
	'createSms'            => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/notifications',
		'Bearer ' . VALID_TOKEN,
		file_get_contents(__DIR__ . '/requests/notifications.createSms.json'),
		StatusCodeInterface::STATUS_CREATED,
		__DIR__ . '/responses/notifications.createSms.json',
	],
	'createSmsNotUnique'   => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/notifications',
		'Bearer ' . VALID_TOKEN,
		file_get_contents(__DIR__ . '/requests/notifications.createSmsNotUnique.unique.json'),
		StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
		__DIR__ . '/responses/notifications.createSmsNotUnique.unique.json',
	],
	'missingRequired'      => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/notifications',
		'Bearer ' . VALID_TOKEN,
		file_get_contents(__DIR__ . '/requests/notifications.create.missing.required.json'),
		StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
		__DIR__ . '/responses/notifications.create.missing.required.json',
	],
	'invalidType'          => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/notifications',
		'Bearer ' . VALID_TOKEN,
		file_get_contents(__DIR__ . '/requests/notifications.create.invalidType.json'),
		StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
		__DIR__ . '/responses/notifications.create.invalidType.json',
	],
];
