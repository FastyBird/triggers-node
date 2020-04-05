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

	// Channels
	public const RABBIT_MQ_CHANNELS_DELETED_ENTITY_ROUTING_KEY = 'fb.bus.node.entity.deleted.device.channel';

	public const RABBIT_MQ_CHANNELS_PROPERTY_DELETED_ENTITY_ROUTING_KEY = 'fb.bus.node.entity.deleted.device.channel.property';

	public const RABBIT_MQ_DEVICES_CHANNELS_PROPERTIES_DATA_ROUTING_KEY = 'fb.bus.node.data.device.channel.property';

	public const RABBIT_MQ_DEVICES_ENTITIES_ROUTING_KEY = [
		'fb.bus.node.entity.*.device',                // Entities
		'fb.bus.node.entity.*.device.*',              // Entities
		'fb.bus.node.data.device.*',                  // Data
	];

	public const RABBIT_MQ_CHANNELS_ENTITIES_ROUTING_KEY = [
		'fb.bus.node.entity.*.device.channel',        // Entities
		'fb.bus.node.entity.*.device.channel.*',      // Entities
		'fb.bus.node.data.device.channel.*',          // Data
	];

}
