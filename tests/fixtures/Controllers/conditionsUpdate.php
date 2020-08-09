<?php declare(strict_types = 1);

use Fig\Http\Message\StatusCodeInterface;

const VALID_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJqdGkiOiI5YWY1NjI0Mi01ZDg3LTQzNjQtYmIxZS1kOWZjODI4NmIzZmYiLCJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbm9kZSIsImlhdCI6MTU4NTc0MjQwMCwiZXhwIjoxNTg1NzQ5NjAwLCJ1c2VyIjoiNWU3OWVmYmYtYmQwZC01YjdjLTQ2ZWYtYmZiZGVmYmZiZDM0Iiwicm9sZXMiOlsiYWRtaW5pc3RyYXRvciJdfQ.Lb-zUa9DL7swdVSEuPTqaR9FvLgKwuEtrhxiJFWjhU8';
const EXPIRED_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJqdGkiOiI3MjFlMTAyNS04Zjc4LTQzOGQtODIwZi0wZDQ2OWEzNzk1NWQiLCJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbm9kZSIsImlhdCI6MTU3Nzg4MDAwMCwiZXhwIjoxNTc3OTAxNjAwLCJ1c2VyIjoiNTI1ZDZhMDktN2MwNi00NmQyLWFmZmEtNzA5YmIxODM3MDdlIiwicm9sZXMiOlsiYWRtaW5pc3RyYXRvciJdfQ.F9veOiNfcqQVxpbMF7OY5j1AcPLpPQb8dEIZbrBmh24';
const INVALID_TOKEN = 'eyJqdGkiOiI5YWY1NjI0Mi01ZDg3LTQzNjQtYmIxZS1kOWZjODI4NmIzZmYiLCJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbm9kZSIsImlhdCI6MTU4NTc0MjQwMCwiZXhwIjoxNTg1NzQ5NjAwLCJ1c2VyIjoiNWU3OWVmYmYtYmQwZC01YjdjLTQ2ZWYtYmZiZGVmYmZiZDM0Iiwicm9sZXMiOlsiYWRtaW5pc3RyYXRvciJdfQ.Lb-zUa9DL7swdVSEuPTqaR9FvLgKwuEtrhxiJFWjhU8';
const VALID_TOKEN_USER = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJqdGkiOiI3YzVkNzdhZC1kOTNlLTRjMmMtOThlNS05ZTFhZmM0NDQ2MTUiLCJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbm9kZSIsImlhdCI6MTU4NTc0MjQwMCwiZXhwIjoxNTg1NzQ5NjAwLCJ1c2VyIjoiZWZiZmJkZWYtYmZiZC02OGVmLWJmYmQtNzcwYjQwZWZiZmJkIiwicm9sZXMiOlsidXNlciJdfQ.cbatWCuGX-K8XbF9MMN7DqxV9hriWmUSGcDGGmnxXX0';

return [
	// Valid responses
	//////////////////
	'update'           => [
		'/v1/triggers/1b17bcaa-a19e-45f0-98b4-56211cc648ae/conditions/09c453b3-c55f-4050-8f1c-b50f8d5728c2',
		'Bearer ' . VALID_TOKEN,
		file_get_contents(__DIR__ . '/requests/conditions.update.json'),
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/conditions.update.json',
	],

	// Invalid responses
	////////////////////
	'notAllowed'       => [
		'/v1/triggers/1b17bcaa-a19e-45f0-98b4-56211cc648ae/conditions/09c453b3-c55f-4050-8f1c-b50f8d5728c2',
		'Bearer ' . VALID_TOKEN_USER,
		file_get_contents(__DIR__ . '/requests/conditions.update.json'),
		StatusCodeInterface::STATUS_FORBIDDEN,
		__DIR__ . '/responses/forbidden.json',
	],
	'unknownTrigger'   => [
		'/v1/triggers/74e40f3e-84cb-4e0c-b3b3-fbf8246e0888/conditions/09c453b3-c55f-4050-8f1c-b50f8d5728c2',
		'Bearer ' . VALID_TOKEN,
		file_get_contents(__DIR__ . '/requests/conditions.update.json'),
		StatusCodeInterface::STATUS_NOT_FOUND,
		__DIR__ . '/responses/triggers.notFound.json',
	],
	'unknownCondition' => [
		'/v1/triggers/1b17bcaa-a19e-45f0-98b4-56211cc648ae/conditions/74e40f3e-84cb-4e0c-b3b3-fbf8246e0888',
		'Bearer ' . VALID_TOKEN,
		file_get_contents(__DIR__ . '/requests/conditions.update.json'),
		StatusCodeInterface::STATUS_NOT_FOUND,
		__DIR__ . '/responses/conditions.notFound.json',
	],
	'invalidType'      => [
		'/v1/triggers/1b17bcaa-a19e-45f0-98b4-56211cc648ae/conditions/09c453b3-c55f-4050-8f1c-b50f8d5728c2',
		'Bearer ' . VALID_TOKEN,
		file_get_contents(__DIR__ . '/requests/conditions.update.invalidType.json'),
		StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
		__DIR__ . '/responses/conditions.update.invalidType.json',
	],
	'idMismatch'       => [
		'/v1/triggers/1b17bcaa-a19e-45f0-98b4-56211cc648ae/conditions/09c453b3-c55f-4050-8f1c-b50f8d5728c2',
		'Bearer ' . VALID_TOKEN,
		file_get_contents(__DIR__ . '/requests/conditions.update.idMismatch.json'),
		StatusCodeInterface::STATUS_BAD_REQUEST,
		__DIR__ . '/responses/invalid.identifier.json',
	],
	'missingToken'     => [
		'/v1/triggers/1b17bcaa-a19e-45f0-98b4-56211cc648ae/conditions/09c453b3-c55f-4050-8f1c-b50f8d5728c2',
		null,
		file_get_contents(__DIR__ . '/requests/conditions.update.json'),
		StatusCodeInterface::STATUS_FORBIDDEN,
		__DIR__ . '/responses/forbidden.json',
	],
	'emptyToken'       => [
		'/v1/triggers/1b17bcaa-a19e-45f0-98b4-56211cc648ae/conditions/09c453b3-c55f-4050-8f1c-b50f8d5728c2',
		'',
		file_get_contents(__DIR__ . '/requests/conditions.update.json'),
		StatusCodeInterface::STATUS_FORBIDDEN,
		__DIR__ . '/responses/forbidden.json',
	],
	'invalidToken'     => [
		'/v1/triggers/1b17bcaa-a19e-45f0-98b4-56211cc648ae/conditions/09c453b3-c55f-4050-8f1c-b50f8d5728c2',
		'Bearer ' . INVALID_TOKEN,
		file_get_contents(__DIR__ . '/requests/conditions.update.json'),
		StatusCodeInterface::STATUS_UNAUTHORIZED,
		__DIR__ . '/responses/unauthorized.json',
	],
	'expiredToken'     => [
		'/v1/triggers/1b17bcaa-a19e-45f0-98b4-56211cc648ae/conditions/09c453b3-c55f-4050-8f1c-b50f8d5728c2',
		'Bearer ' . EXPIRED_TOKEN,
		file_get_contents(__DIR__ . '/requests/conditions.update.json'),
		StatusCodeInterface::STATUS_UNAUTHORIZED,
		__DIR__ . '/responses/unauthorized.json',
	],
];
