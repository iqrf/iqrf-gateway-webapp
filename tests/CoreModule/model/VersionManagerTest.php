<?php

/**
 * TEST: App\CoreModule\Model\VersionManager
 * @covers App\CoreModule\Model\VersionManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\CoreModule\Model;

use App\CoreModule\Model\CommandManager;
use App\CoreModule\Model\VersionManager;
use Nette\Caching\Storages\DevNullStorage;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

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
	 * @var \Mockery\MockInterface Mocked command manager
	 */
	private $commandManagerMocked;

	/**
	 * @var string Current version of the webapp
	 */
	private $currentVersion = '2.0.0-beta';

	/**
	 * @var string Current stable version of the webapp
	 */
	private $stableVersion = '1.1.6';

	/**
	 * @var VersionManager Version manager
	 */
	private $manager;

	/**
	 * @var \Mockery\Mock Mocked version manager
	 */
	private $managerMocked;

	/**
	 * Constructor
	 * @param Container $container Nette Tester Container
	 */
	public function __construct(Container $container) {
		$this->container = $container;
	}

	/**
	 * Set up the test environment
	 */
	protected function setUp(): void {
		$this->commandManager = new CommandManager(false);
		$this->commandManagerMocked = \Mockery::mock(CommandManager::class);
		$this->cacheStorage = new DevNullStorage();
		$this->manager = new VersionManager($this->commandManager, $this->cacheStorage);
		$this->managerMocked = \Mockery::mock(VersionManager::class)->makePartial();
	}

	/**
	 * Cleanup the test environment
	 */
	protected function tearDown(): void {
		\Mockery::close();
	}

	/**
	 * Test function to check if an update is available for the webapp
	 */
	public function testAvailableWebappUpdateNo(): void {
		$this->managerMocked->shouldReceive('getInstalledWebapp')->with(false)->andReturn($this->currentVersion);
		$this->managerMocked->shouldReceive('getCurrentWebapp')->with()->andReturn($this->currentVersion);
		Assert::false($this->managerMocked->availableWebappUpdate());
	}

	/**
	 * Test function to check if an update is available for the webapp
	 */
	public function testAvailableWebappUpdateYes(): void {
		$this->managerMocked->shouldReceive('getInstalledWebapp')->with(false)->andReturn('1.1.4');
		$this->managerMocked->shouldReceive('getCurrentWebapp')->with()->andReturn($this->currentVersion);
		Assert::true($this->managerMocked->availableWebappUpdate());
	}

	/**
	 * Test function to get the current stable version of the webapp
	 */
	public function testGetCurrentWebapp(): void {
		Assert::same($this->stableVersion, $this->manager->getCurrentWebapp());
	}

	/**
	 * Test function to get version of the webapp (with git)
	 */
	public function testGetInstalledWebappWithGit(): void {
		$expected = 'v' . $this->currentVersion . ' (master - 733d45340cbb2565fd068ca3257ad39a5e46f963)';
		$gitBranches = '* master                 733d45340cbb2565fd068ca3257ad39a5e46f963 Add a notification to an update webapp to newer stable version';
		$this->commandManagerMocked->shouldReceive('commandExist')->with('git')->andReturn(true);
		$this->commandManagerMocked->shouldReceive('send')->with('git branch -v --no-abbrev')->andReturn($gitBranches);
		$manager = new VersionManager($this->commandManagerMocked, $this->cacheStorage);
		Assert::same($expected, $manager->getInstalledWebapp());
	}

	/**
	 * Test function to get version of the webapp (without git)
	 */
	public function testGetInstalledWebappWithoutGit(): void {
		$expected = 'v' . $this->currentVersion;
		$this->commandManagerMocked->shouldReceive('commandExist')->with('git')->andReturn(false);
		$manager = new VersionManager($this->commandManagerMocked, $this->cacheStorage);
		Assert::same($expected, $manager->getInstalledWebapp());
	}

}

$test = new VersionManagerTest($container);
$test->run();
