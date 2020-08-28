<?php declare(strict_types = 1);

/**
 * Constants.php
 *
 * @license        More in license.md
 * @copyright      https://fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:TriggersNode!
 * @subpackage     common
 * @since          0.1.0
 *
 * @date           05.04.20
 */

namespace FastyBird\TriggersNode;

use FastyBird\TriggersNode\Entities as TriggersNodeEntities;

/**
 * Service constants
 *
 * @package        FastyBird:TriggersNode!
 * @subpackage     common
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class Constants
{

	/**
	 * Node routing
	 */

	public const ROUTE_NAME_TRIGGERS = 'triggers';
	public const ROUTE_NAME_TRIGGER = 'trigger';
	public const ROUTE_NAME_TRIGGER_RELATIONSHIP = 'trigger.relationship';

	/**
	 * Message bus routing keys mapping
	 */
	public const RABBIT_MQ_ENTITIES_ROUTING_KEYS_MAPPING = [
		TriggersNodeEntities\Triggers\Trigger::class           => 'fb.bus.node.entity.[ACTION].trigger',
		TriggersNodeEntities\Actions\Action::class             => 'fb.bus.node.entity.[ACTION].trigger.action',
		TriggersNodeEntities\Notifications\Notification::class => 'fb.bus.node.entity.[ACTION].trigger.notification',
		TriggersNodeEntities\Conditions\Condition::class       => 'fb.bus.node.entity.[ACTION].trigger.condition',
	];

	public const RABBIT_MQ_ENTITIES_ROUTING_KEY_ACTION_REPLACE_STRING = '[ACTION]';

	/**
	 * Message bus routing key for devices properties messages
	 */

	// Devices
	public const RABBIT_MQ_DEVICES_DELETED_ENTITY_ROUTING_KEY = 'fb.bus.node.entity.deleted.device';

	public const RABBIT_MQ_DEVICES_PROPERTY_UPDATED_ENTITY_ROUTING_KEY = 'fb.bus.node.entity.updated.device.property';
	public const RABBIT_MQ_DEVICES_PROPERTY_DELETED_ENTITY_ROUTING_KEY = 'fb.bus.node.entity.deleted.device.property';

	// Channels
	public const RABBIT_MQ_CHANNELS_DELETED_ENTITY_ROUTING_KEY = 'fb.bus.node.entity.deleted.device.channel';

	public const RABBIT_MQ_CHANNELS_PROPERTY_UPDATED_ENTITY_ROUTING_KEY = 'fb.bus.node.entity.updated.channel.property';
	public const RABBIT_MQ_CHANNELS_PROPERTY_DELETED_ENTITY_ROUTING_KEY = 'fb.bus.node.entity.deleted.channel.property';

	// Data routing keys
	public const RABBIT_MQ_DEVICES_PROPERTIES_DATA_ROUTING_KEY = 'fb.bus.node.data.device.property';
	public const RABBIT_MQ_CHANNELS_PROPERTIES_DATA_ROUTING_KEY = 'fb.bus.node.data.channel.property';

	/**
	 * Microservices origins
	 */

	public const NODE_DEVICES_ORIGIN = 'com.fastybird.devices-node';

	/**
	 * Data types
	 */
	public const DATA_TYPE_INTEGER = 'integer';
	public const DATA_TYPE_FLOAT = 'float';
	public const DATA_TYPE_BOOLEAN = 'boolean';
	public const DATA_TYPE_STRING = 'string';
	public const DATA_TYPE_ENUM = 'enum';
	public const DATA_TYPE_COLOR = 'color';

}
