<?php declare(strict_types = 1);

use FastyBird\TriggersNode;
use Nette\Utils;

return [
	'messageWithDeletedChannelProperty'     => [
		TriggersNode\Constants::RABBIT_MQ_CHANNELS_PROPERTY_DELETED_ENTITY_ROUTING_KEY,
		Utils\ArrayHash::from([
			'device'   => 'device-one',
			'channel'  => 'channel-one',
			'property' => 'button',
			'name'     => 'button',
		]),
		2,
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
				'id'       => '5c47a7c0-99d5-4dfa-b289-edb8afe4d198',
				'trigger'  => '1c580923-28dd-4b28-8517-bf37f0173b93',
				'owner'    => null,
				'device'   => 'device-two',
				'channel'  => 'channel-one',
				'property' => 'switch',
				'value'    => 'toggle',
			],
		],
		1,
		0,
		1,
		1,
	],
	'messageWithDeletedChannelProperty_two' => [
		TriggersNode\Constants::RABBIT_MQ_CHANNELS_PROPERTY_DELETED_ENTITY_ROUTING_KEY,
		Utils\ArrayHash::from([
			'device'   => 'device-one',
			'channel'  => 'channel-four',
			'property' => 'switch',
			'name'     => 'switch',
		]),
		1,
		[
			'fb.bus.node.entity.deleted.trigger.action' => [
				'id'       => '4aa84028-d8b7-4128-95b2-295763634aa4',
				'trigger'  => 'c64ba1c4-0eda-4cab-87a0-4d634f7b67f4',
				'owner'    => null,
				'device'   => 'device-one',
				'channel'  => 'channel-four',
				'property' => 'switch',
				'value'    => 'on',
			],
		],
		1,
		1,
		1,
		0,
	],
];