<?php

/**
 * TEST: App\Model\VersionManager
 * @covers App\Model\VersionManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\Model;

use App\Model\CommandManager;
use App\Model\VersionManager;
use Nette\Caching\Storages\DevNullStorage;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../bootstrap.php';

/**
 * Tests for version manager
 */
class VersionManagerTest extends TestCase {

	/**
	 * @var DevNullStorage Cache storage for testing
	 */
	private $cacheStorage;

	/**
	 * @var Container Nette Tester Container
	 */
	private $container;

	/**
	 * @var CommandManager Command manager
	 */
	private $commandManager;

	/**
	 * @var string Current version of the webapp
	 */
	private $currentVersion = '1.1.6';

	/**
	 * @var VersionManager Version manager
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
	 * Set up test environment
	 */
	public function setUp() {
		$this->commandManager = new CommandManager(false);
		$this->cacheStorage = new DevNullStorage();
		$this->manager = new VersionManager($this->commandManager, $this->cacheStorage);
	}

	/**
	 * Test function to check if an update is available for the webapp
	 */
	public function testAvailableWebappUpdate() {
		$manager0 = \Mockery::mock(VersionManager::class)->makePartial();
		$manager0->shouldReceive('getInstalledWebapp')->with(false)->andReturn($this->currentVersion);
		$manager0->shouldReceive('getCurrentWebapp')->with()->andReturn($this->currentVersion);
		Assert::false($manager0->availableWebappUpdate());
		$manager1 = \Mockery::mock(VersionManager::class)->makePartial();
		$manager1->shouldReceive('getInstalledWebapp')->with(false)->andReturn('1.1.4');
		$manager1->shouldReceive('getCurrentWebapp')->with()->andReturn($this->currentVersion);
		Assert::true($manager1->availableWebappUpdate());
	}

	/**
	 * Test function to get the current stable version of the webapp
	 */
	public function testGetCurrentWebapp() {
		Assert::same($this->currentVersion, $this->manager->getCurrentWebapp());
	}

	/**
	 * Test function to get version of the webapp (with git)
	 */
	public function testGetInstalledWebappWithGit() {
		$expected = 'v' . $this->currentVersion . ' (master - 733d45340cbb2565fd068ca3257ad39a5e46f963)';
		$gitBranches = '* master                 733d45340cbb2565fd068ca3257ad39a5e46f963 Add a notification to an update webapp to newer stable version';
		$commandManager = \Mockery::mock(CommandManager::class);
		$commandManager->shouldReceive('commandExist')->with('git')->andReturn(true);
		$commandManager->shouldReceive('send')->with('git branch -v --no-abbrev')->andReturn($gitBranches);
		$manager = new VersionManager($commandManager, $this->cacheStorage);
		Assert::same($expected, $manager->getInstalledWebapp());
	}

	/**
	 * Test function to get version of the webapp (without git)
	 */
	public function testGetInstalledWebappWithoutGit() {
		$expected = 'v' . $this->currentVersion;
		$commandManager = \Mockery::mock(CommandManager::class);
		$commandManager->shouldReceive('commandExist')->with('git')->andReturn(false);
		$manager = new VersionManager($commandManager, $this->cacheStorage);
		Assert::same($expected, $manager->getInstalledWebapp());
	}

}

$test = new VersionManagerTest($container);
$test->run();
