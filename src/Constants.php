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

use FastyBird\TriggersModule\Entities as TriggersModuleEntities;

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
	 * Message bus routing keys mapping
	 */
	public const RABBIT_MQ_ENTITIES_ROUTING_KEYS_MAPPING = [
		TriggersModuleEntities\Triggers\Trigger::class           => 'fb.bus.node.entity.[ACTION].trigger',
		TriggersModuleEntities\Actions\Action::class             => 'fb.bus.node.entity.[ACTION].trigger.action',
		TriggersModuleEntities\Notifications\Notification::class => 'fb.bus.node.entity.[ACTION].trigger.notification',
		TriggersModuleEntities\Conditions\Condition::class       => 'fb.bus.node.entity.[ACTION].trigger.condition',
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

}
