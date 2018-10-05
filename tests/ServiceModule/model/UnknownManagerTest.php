<?php

/**
 * TEST: App\ServiceModule\Model\UnknownManager
 * @covers App\ServiceModule\Model\UnknownManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\ServiceModule\Model;

use App\CoreModule\Model\CommandManager;
use App\ServiceModule\Exception\NotSupportedInitSystemException;
use App\ServiceModule\Model\UnknownManager;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for service manager
 */
class UnknownManagerTest extends TestCase {

	/**
	 * @var Container Nette Tester Container
	 */
	private $container;

	/**
	 * @var \Mockery\MockInterface Mocked command manager
	 */
	private $commandManager;

	/**
	 * @var UnknownManager Service manager for unknown init daemon
	 */
	private $manager;

	/**
	 * Constructor
	 * @param Container $container Nette Tester Container
	 */
	public function __construct(Container $container) {
		$this->container = $container;
	}

	/**
	 * Test function to start IQRF Gateway Daemon's service via unknown init daemon
	 */
	public function testStart(): void {
		Assert::exception([$this->manager, 'start'], NotSupportedInitSystemException::class);
	}

	/**
	 * Test function to stop IQRF Gateway Daemon's service via unknown init daemon
	 */
	public function testStop(): void {
		Assert::exception([$this->manager, 'stop'], NotSupportedInitSystemException::class);
	}

	/**
	 * Test function to restart IQRF Gateway Daemon's service via unknown init daemon
	 */
	public function testRestart(): void {
		Assert::exception([$this->manager, 'restart'], NotSupportedInitSystemException::class);
	}

	/**
	 * Test function to get status of IQRF Gateway Daemon's service via unknown init daemon
	 */
	public function testGetStatus(): void {
		Assert::exception([$this->manager, 'getStatus'], NotSupportedInitSystemException::class);
	}

	/**
	 * Set up the test environment
	 */
	protected function setUp(): void {
		$this->commandManager = \Mockery::mock(CommandManager::class);
		$this->manager = new UnknownManager($this->commandManager);
	}

	/**
	 * Cleanup the test environment
	 */
	protected function tearDown(): void {
		\Mockery::close();
	}

}

$test = new UnknownManagerTest($container);
$test->run();
