<?php declare(strict_types = 1);

use FastyBird\TriggersNode;
use Nette\Utils;

return [
	'messageWithDeletedChannel' => [
		TriggersNode\Constants::RABBIT_MQ_CHANNELS_DELETED_ENTITY_ROUTING_KEY,
		Utils\ArrayHash::from([
			'device'  => 'device-one',
			'channel' => 'channel-one',
			'name'    => 'Channel one',
		]),
		3,
		[
			'fb.bus.node.entity.deleted.trigger'        => [
				'id'         => '1c580923-28dd-4b28-8517-bf37f0173b93',
				'name'       => '28bc0d38-2f7c-4a71-aa74-27b102f8df4c',
				'comment'    => '996213a4-d959-4f6c-b77c-f248ce8f8d84',
				'is_enabled' => true,
				'owner'      => null,
				'device'     => 'device-one',
				'channel'    => 'channel-one',
				'property'   => 'button',
				'operand'    => '3',
				'operator'   => 'eq',
			],
			'fb.bus.node.entity.deleted.trigger.action' => [
				'primaryKey'                           => 'id',
				'5c47a7c0-99d5-4dfa-b289-edb8afe4d198' => [
					'id'       => '5c47a7c0-99d5-4dfa-b289-edb8afe4d198',
					'trigger'  => '1c580923-28dd-4b28-8517-bf37f0173b93',
					'owner'    => null,
					'device'   => 'device-two',
					'channel'  => 'channel-one',
					'property' => 'switch',
					'value'    => 'toggle',
				],
				'0dac7180-dfe1-4079-ba91-fec6eeccccdf' => [
					'id'       => '0dac7180-dfe1-4079-ba91-fec6eeccccdf',
					'trigger'  => '402aabb9-b5a8-4f28-aad4-c7ec245831b2',
					'owner'    => null,
					'device'   => 'device-one',
					'channel'  => 'channel-one',
					'property' => 'switch',
					'value'    => 'toggle',
				]
			],
		],
	],
];