<?php declare(strict_types = 1);

use Fig\Http\Message\StatusCodeInterface;

const VALID_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJqdGkiOiI5YWY1NjI0Mi01ZDg3LTQzNjQtYmIxZS1kOWZjODI4NmIzZmYiLCJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbm9kZSIsImlhdCI6MTU4NTc0MjQwMCwiZXhwIjoxNTg1NzQ5NjAwLCJ1c2VyIjoiNWU3OWVmYmYtYmQwZC01YjdjLTQ2ZWYtYmZiZGVmYmZiZDM0Iiwicm9sZXMiOlsiYWRtaW5pc3RyYXRvciJdfQ.Lb-zUa9DL7swdVSEuPTqaR9FvLgKwuEtrhxiJFWjhU8';
const EXPIRED_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJqdGkiOiI3MjFlMTAyNS04Zjc4LTQzOGQtODIwZi0wZDQ2OWEzNzk1NWQiLCJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbm9kZSIsImlhdCI6MTU3Nzg4MDAwMCwiZXhwIjoxNTc3OTAxNjAwLCJ1c2VyIjoiNTI1ZDZhMDktN2MwNi00NmQyLWFmZmEtNzA5YmIxODM3MDdlIiwicm9sZXMiOlsiYWRtaW5pc3RyYXRvciJdfQ.F9veOiNfcqQVxpbMF7OY5j1AcPLpPQb8dEIZbrBmh24';
const INVALID_TOKEN = 'eyJqdGkiOiI5YWY1NjI0Mi01ZDg3LTQzNjQtYmIxZS1kOWZjODI4NmIzZmYiLCJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbm9kZSIsImlhdCI6MTU4NTc0MjQwMCwiZXhwIjoxNTg1NzQ5NjAwLCJ1c2VyIjoiNWU3OWVmYmYtYmQwZC01YjdjLTQ2ZWYtYmZiZGVmYmZiZDM0Iiwicm9sZXMiOlsiYWRtaW5pc3RyYXRvciJdfQ.Lb-zUa9DL7swdVSEuPTqaR9FvLgKwuEtrhxiJFWjhU8';
const VALID_TOKEN_USER = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJqdGkiOiI3YzVkNzdhZC1kOTNlLTRjMmMtOThlNS05ZTFhZmM0NDQ2MTUiLCJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbm9kZSIsImlhdCI6MTU4NTc0MjQwMCwiZXhwIjoxNTg1NzQ5NjAwLCJ1c2VyIjoiZWZiZmJkZWYtYmZiZC02OGVmLWJmYmQtNzcwYjQwZWZiZmJkIiwicm9sZXMiOlsidXNlciJdfQ.cbatWCuGX-K8XbF9MMN7DqxV9hriWmUSGcDGGmnxXX0';

return [
	'createChannelProperty' => [
		'/v1/triggers/1b17bcaa-a19e-45f0-98b4-56211cc648ae/conditions',
		'Bearer ' . VALID_TOKEN,
		file_get_contents(__DIR__ . '/requests/conditions.createChannelProperty.json'),
		StatusCodeInterface::STATUS_CREATED,
		__DIR__ . '/responses/conditions.createChannelProperty.json',
	],
	'createDeviceProperty'  => [
		'/v1/triggers/1b17bcaa-a19e-45f0-98b4-56211cc648ae/conditions',
		'Bearer ' . VALID_TOKEN,
		file_get_contents(__DIR__ . '/requests/conditions.createDeviceProperty.json'),
		StatusCodeInterface::STATUS_CREATED,
		__DIR__ . '/responses/conditions.createDeviceProperty.json',
	],
	'createTime'            => [
		'/v1/triggers/1b17bcaa-a19e-45f0-98b4-56211cc648ae/conditions',
		'Bearer ' . VALID_TOKEN,
		file_get_contents(__DIR__ . '/requests/conditions.createTime.json'),
		StatusCodeInterface::STATUS_CREATED,
		__DIR__ . '/responses/conditions.createTime.json',
	],
	'missingRequired'       => [
		'/v1/triggers/1b17bcaa-a19e-45f0-98b4-56211cc648ae/conditions',
		'Bearer ' . VALID_TOKEN,
		file_get_contents(__DIR__ . '/requests/conditions.create.missing.required.json'),
		StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
		__DIR__ . '/responses/conditions.create.missing.required.json',
	],
	'invalidType'           => [
		'/v1/triggers/1b17bcaa-a19e-45f0-98b4-56211cc648ae/conditions',
		'Bearer ' . VALID_TOKEN,
		file_get_contents(__DIR__ . '/requests/conditions.create.invalidType.json'),
		StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
		__DIR__ . '/responses/conditions.create.invalidType.json',
	],
];
