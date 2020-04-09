<?php declare(strict_types = 1);

/**
 * Constants.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:TriggersNode!
 * @subpackage     common
 * @since          0.1.0
 *
 * @date           05.04.20
 */

namespace FastyBird\TriggersNode;

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
	 * Message bus routing key for devices properties messages
	 */

	// Devices
	public const RABBIT_MQ_DEVICES_DELETED_ENTITY_ROUTING_KEY = 'fb.bus.node.entity.deleted.device';

	public const RABBIT_MQ_DEVICES_PROPERTY_DELETED_ENTITY_ROUTING_KEY = 'fb.bus.node.entity.deleted.device.property';

	public const RABBIT_MQ_DEVICES_PROPERTIES_DATA_ROUTING_KEY = 'fb.bus.node.data.device.property';

	// Devices properties
	public const RABBIT_MQ_ENTITY_STORAGE_DEVICE_PROPERTY_CREATED_ROUTING_KEY = 'fb.bus.node.entity.created.storage.property';
	public const RABBIT_MQ_ENTITY_STORAGE_DEVICE_PROPERTY_UPDATED_ROUTING_KEY = 'fb.bus.node.entity.updated.storage.property';

	// Channels
	public const RABBIT_MQ_CHANNELS_DELETED_ENTITY_ROUTING_KEY = 'fb.bus.node.entity.deleted.device.channel';

	public const RABBIT_MQ_CHANNELS_PROPERTY_DELETED_ENTITY_ROUTING_KEY = 'fb.bus.node.entity.deleted.device.channel.property';

	public const RABBIT_MQ_DEVICES_CHANNELS_PROPERTIES_DATA_ROUTING_KEY = 'fb.bus.node.data.device.channel.property';

	// Channels properties
	public const RABBIT_MQ_ENTITY_STORAGE_CHANNEL_PROPERTY_CREATED_ROUTING_KEY = 'fb.bus.node.entity.created.storage.channel.property';
	public const RABBIT_MQ_ENTITY_STORAGE_CHANNEL_PROPERTY_UPDATED_ROUTING_KEY = 'fb.bus.node.entity.updated.storage.channel.property';

	// Data routing keys
	public const RABBIT_MQ_DATA_DEVICE_PROPERTY_ROUTING_KEY = 'fb.bus.node.data.device.property';
	public const RABBIT_MQ_DATA_CHANNEL_PROPERTY_ROUTING_KEY = 'fb.bus.node.data.channel.property';

	// Bindings keys
	public const RABBIT_MQ_DEVICES_ENTITIES_ROUTING_KEY = [
		'fb.bus.node.entity.*.device',                // Entities
		'fb.bus.node.entity.*.device.*',              // Entities
	];

	public const RABBIT_MQ_CHANNELS_ENTITIES_ROUTING_KEY = [
		'fb.bus.node.entity.*.device.channel',        // Entities
		'fb.bus.node.entity.*.device.channel.*',      // Entities
	];

	public const RABBIT_MQ_STORAGE_ENTITIES_ROUTING_KEY = [
		'fb.bus.node.entity.*.storage.*',            // Entities
		'fb.bus.node.entity.*.storage.channel.*',    // Entities
	];

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
