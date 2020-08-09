<?php declare(strict_types = 1);

use Fig\Http\Message\StatusCodeInterface;

const VALID_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJqdGkiOiI5YWY1NjI0Mi01ZDg3LTQzNjQtYmIxZS1kOWZjODI4NmIzZmYiLCJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbm9kZSIsImlhdCI6MTU4NTc0MjQwMCwiZXhwIjoxNTg1NzQ5NjAwLCJ1c2VyIjoiNWU3OWVmYmYtYmQwZC01YjdjLTQ2ZWYtYmZiZGVmYmZiZDM0Iiwicm9sZXMiOlsiYWRtaW5pc3RyYXRvciJdfQ.Lb-zUa9DL7swdVSEuPTqaR9FvLgKwuEtrhxiJFWjhU8';
const EXPIRED_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJqdGkiOiI3MjFlMTAyNS04Zjc4LTQzOGQtODIwZi0wZDQ2OWEzNzk1NWQiLCJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbm9kZSIsImlhdCI6MTU3Nzg4MDAwMCwiZXhwIjoxNTc3OTAxNjAwLCJ1c2VyIjoiNTI1ZDZhMDktN2MwNi00NmQyLWFmZmEtNzA5YmIxODM3MDdlIiwicm9sZXMiOlsiYWRtaW5pc3RyYXRvciJdfQ.F9veOiNfcqQVxpbMF7OY5j1AcPLpPQb8dEIZbrBmh24';
const INVALID_TOKEN = 'eyJqdGkiOiI5YWY1NjI0Mi01ZDg3LTQzNjQtYmIxZS1kOWZjODI4NmIzZmYiLCJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbm9kZSIsImlhdCI6MTU4NTc0MjQwMCwiZXhwIjoxNTg1NzQ5NjAwLCJ1c2VyIjoiNWU3OWVmYmYtYmQwZC01YjdjLTQ2ZWYtYmZiZGVmYmZiZDM0Iiwicm9sZXMiOlsiYWRtaW5pc3RyYXRvciJdfQ.Lb-zUa9DL7swdVSEuPTqaR9FvLgKwuEtrhxiJFWjhU8';
const VALID_TOKEN_USER = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJqdGkiOiI3YzVkNzdhZC1kOTNlLTRjMmMtOThlNS05ZTFhZmM0NDQ2MTUiLCJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbm9kZSIsImlhdCI6MTU4NTc0MjQwMCwiZXhwIjoxNTg1NzQ5NjAwLCJ1c2VyIjoiZWZiZmJkZWYtYmZiZC02OGVmLWJmYmQtNzcwYjQwZWZiZmJkIiwicm9sZXMiOlsidXNlciJdfQ.cbatWCuGX-K8XbF9MMN7DqxV9hriWmUSGcDGGmnxXX0';

return [
	// Valid responses
	//////////////////
	'delete'         => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/actions/4aa84028-d8b7-4128-95b2-295763634aa4',
		'Bearer ' . VALID_TOKEN,
		StatusCodeInterface::STATUS_NO_CONTENT,
		__DIR__ . '/responses/actions.delete.json',
	],

	// Invalid responses
	////////////////////
	'notAllowed'     => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/actions/4aa84028-d8b7-4128-95b2-295763634aa4',
		'Bearer ' . VALID_TOKEN_USER,
		StatusCodeInterface::STATUS_FORBIDDEN,
		__DIR__ . '/responses/forbidden.json',
	],
	'deleteUnknown'  => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/actions/74e40f3e-84cb-4e0c-b3b3-fbf8246e0888',
		'Bearer ' . VALID_TOKEN,
		StatusCodeInterface::STATUS_NOT_FOUND,
		__DIR__ . '/responses/actions.notFound.json',
	],
	'unknownTrigger' => [
		'/v1/triggers/74e40f3e-84cb-4e0c-b3b3-fbf8246e0888/actions/4aa84028-d8b7-4128-95b2-295763634aa4',
		'Bearer ' . VALID_TOKEN,
		StatusCodeInterface::STATUS_NOT_FOUND,
		__DIR__ . '/responses/triggers.notFound.json',
	],
	'missingToken'   => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/actions/4aa84028-d8b7-4128-95b2-295763634aa4',
		null,
		StatusCodeInterface::STATUS_FORBIDDEN,
		__DIR__ . '/responses/forbidden.json',
	],
	'emptyToken'     => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/actions/4aa84028-d8b7-4128-95b2-295763634aa4',
		'',
		StatusCodeInterface::STATUS_FORBIDDEN,
		__DIR__ . '/responses/forbidden.json',
	],
	'invalidToken'   => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/actions/4aa84028-d8b7-4128-95b2-295763634aa4',
		'Bearer ' . INVALID_TOKEN,
		StatusCodeInterface::STATUS_UNAUTHORIZED,
		__DIR__ . '/responses/unauthorized.json',
	],
	'expiredToken'   => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/actions/4aa84028-d8b7-4128-95b2-295763634aa4',
		'Bearer ' . EXPIRED_TOKEN,
		StatusCodeInterface::STATUS_UNAUTHORIZED,
		__DIR__ . '/responses/unauthorized.json',
	],
];
