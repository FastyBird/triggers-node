<?php declare(strict_types = 1);

/**
 * ResponseHandler.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:TriggersNode!
 * @subpackage     Events
 * @since          0.1.0
 *
 * @date           15.04.20
 */

namespace FastyBird\TriggersNode\Events;

use Doctrine\Common;
use Nette;

/**
 * Before http request processed handler
 *
 * @package         FastyBird:TriggersNode!
 * @subpackage      Events
 *
 * @author          Adam Kadlec <adam.kadlec@fastybird.com>
 */
class ResponseHandler
{

	use Nette\SmartObject;

	/** @var Common\Persistence\ManagerRegistry */
	private $managerRegistry;

	public function __construct(
		Common\Persistence\ManagerRegistry $managerRegistry
	) {
		$this->managerRegistry = $managerRegistry;
	}

	/**
	 * @return void
	 */
	public function __invoke(): void
	{
		$em = $this->managerRegistry->getManager();

		// Flushing and then clearing Doctrine's entity manager allows
		// for more memory to be released by PHP
		$em->flush();
		$em->clear();

		// Just in case PHP would choose not to run garbage collection,
		// we run it manually at the end of each batch so that memory is
		// regularly released
		gc_collect_cycles();
	}

}