<?php declare(strict_types = 1);

use Fig\Http\Message\StatusCodeInterface;

const VALID_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJqdGkiOiI5YWY1NjI0Mi01ZDg3LTQzNjQtYmIxZS1kOWZjODI4NmIzZmYiLCJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbm9kZSIsImlhdCI6MTU4NTc0MjQwMCwiZXhwIjoxNTg1NzQ5NjAwLCJ1c2VyIjoiNWU3OWVmYmYtYmQwZC01YjdjLTQ2ZWYtYmZiZGVmYmZiZDM0Iiwicm9sZXMiOlsiYWRtaW5pc3RyYXRvciJdfQ.Lb-zUa9DL7swdVSEuPTqaR9FvLgKwuEtrhxiJFWjhU8';
const EXPIRED_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJqdGkiOiI3MjFlMTAyNS04Zjc4LTQzOGQtODIwZi0wZDQ2OWEzNzk1NWQiLCJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbm9kZSIsImlhdCI6MTU3Nzg4MDAwMCwiZXhwIjoxNTc3OTAxNjAwLCJ1c2VyIjoiNTI1ZDZhMDktN2MwNi00NmQyLWFmZmEtNzA5YmIxODM3MDdlIiwicm9sZXMiOlsiYWRtaW5pc3RyYXRvciJdfQ.F9veOiNfcqQVxpbMF7OY5j1AcPLpPQb8dEIZbrBmh24';
const INVALID_TOKEN = 'eyJqdGkiOiI5YWY1NjI0Mi01ZDg3LTQzNjQtYmIxZS1kOWZjODI4NmIzZmYiLCJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbm9kZSIsImlhdCI6MTU4NTc0MjQwMCwiZXhwIjoxNTg1NzQ5NjAwLCJ1c2VyIjoiNWU3OWVmYmYtYmQwZC01YjdjLTQ2ZWYtYmZiZGVmYmZiZDM0Iiwicm9sZXMiOlsiYWRtaW5pc3RyYXRvciJdfQ.Lb-zUa9DL7swdVSEuPTqaR9FvLgKwuEtrhxiJFWjhU8';
const VALID_TOKEN_USER = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJqdGkiOiI3YzVkNzdhZC1kOTNlLTRjMmMtOThlNS05ZTFhZmM0NDQ2MTUiLCJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbm9kZSIsImlhdCI6MTU4NTc0MjQwMCwiZXhwIjoxNTg1NzQ5NjAwLCJ1c2VyIjoiZWZiZmJkZWYtYmZiZC02OGVmLWJmYmQtNzcwYjQwZWZiZmJkIiwicm9sZXMiOlsidXNlciJdfQ.cbatWCuGX-K8XbF9MMN7DqxV9hriWmUSGcDGGmnxXX0';

return [
	'readAll'                  => [
		'/v1/triggers/1b17bcaa-a19e-45f0-98b4-56211cc648ae/conditions',
		'Bearer ' . VALID_TOKEN,
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/conditions.index.json',
	],
	'readAllPaging'            => [
		'/v1/triggers/1b17bcaa-a19e-45f0-98b4-56211cc648ae/conditions?page[offset]=1&page[limit]=1',
		'Bearer ' . VALID_TOKEN,
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/conditions.index.paging.json',
	],
	'readOne'                  => [
		'/v1/triggers/1b17bcaa-a19e-45f0-98b4-56211cc648ae/conditions/09c453b3-c55f-4050-8f1c-b50f8d5728c2',
		'Bearer ' . VALID_TOKEN,
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/conditions.read.json',
	],
	'readOneInclude'           => [
		'/v1/triggers/1b17bcaa-a19e-45f0-98b4-56211cc648ae/conditions/09c453b3-c55f-4050-8f1c-b50f8d5728c2?include=trigger',
		'Bearer ' . VALID_TOKEN,
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/conditions.read.include.json',
	],
	'readOneUnknown'           => [
		'/v1/triggers/1b17bcaa-a19e-45f0-98b4-56211cc648ae/conditions/69786d15-fd0c-4d9f-9378-33287c2009af',
		'Bearer ' . VALID_TOKEN,
		StatusCodeInterface::STATUS_NOT_FOUND,
		__DIR__ . '/responses/conditions.notFound.json',
	],
	'readRelationshipsTrigger' => [
		'/v1/triggers/1b17bcaa-a19e-45f0-98b4-56211cc648ae/conditions/09c453b3-c55f-4050-8f1c-b50f8d5728c2/relationships/trigger',
		'Bearer ' . VALID_TOKEN,
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/conditions.readRelationships.trigger.json',
	],
	'readRelationshipsUnknown' => [
		'/v1/triggers/1b17bcaa-a19e-45f0-98b4-56211cc648ae/conditions/09c453b3-c55f-4050-8f1c-b50f8d5728c2/relationships/unknown',
		'Bearer ' . VALID_TOKEN,
		StatusCodeInterface::STATUS_NOT_FOUND,
		__DIR__ . '/responses/relation.unknown.json',
	],
	'readAllInvalid'           => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/conditions',
		'Bearer ' . VALID_TOKEN,
		StatusCodeInterface::STATUS_BAD_REQUEST,
		__DIR__ . '/responses/conditions.index.invalid.json',
	],
	'readInvalid'              => [
		'/v1/triggers/c64ba1c4-0eda-4cab-87a0-4d634f7b67f4/conditions/09c453b3-c55f-4050-8f1c-b50f8d5728c2',
		'Bearer ' . VALID_TOKEN,
		StatusCodeInterface::STATUS_BAD_REQUEST,
		__DIR__ . '/responses/conditions.index.invalid.json',
	],
];
