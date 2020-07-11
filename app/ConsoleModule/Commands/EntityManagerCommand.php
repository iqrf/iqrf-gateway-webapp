<?php


namespace App\ConsoleModule\Commands;

use App\Models\Database\EntityManager;
use Symfony\Component\Console\Command\Command;

/**
 * CLI command with entity manager
 */
abstract class EntityManagerCommand extends Command {

	/**
	 * @var EntityManager Entity manager
	 */
	protected $entityManager;

	/**
	 * Constructor
	 * @param EntityManager $entityManager Entity manager
	 * @param string|null $name Command name
	 */
	public function __construct(EntityManager $entityManager, ?string $name = null) {
		parent::__construct($name);
		$this->entityManager = $entityManager;
	}

}
