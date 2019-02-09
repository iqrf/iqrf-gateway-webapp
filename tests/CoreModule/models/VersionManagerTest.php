<?php

/**
 * TEST: App\CoreModule\Models\VersionManager
 * @covers App\CoreModule\Models\VersionManager
 * @phpVersion >= 7.1
 * @testCase
 */
declare(strict_types = 1);

namespace Test\CoreModule\Model;

use App\CoreModule\Models\VersionManager;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Mockery\Mock;
use Nette\Caching\Storages\DevNullStorage;
use Tester\Assert;
use Tests\Toolkit\TestCases\CommandTestCase;

require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for version manager
 */
class VersionManagerTest extends CommandTestCase {

	/**
	 * @var DevNullStorage Cache storage for testing
	 */
	private $cacheStorage;

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
	 * @var Mock|VersionManager Mocked version manager
	 */
	private $managerMocked;

	/**
	 * Tests the function to check if an update is available for the webapp
	 */
	public function testAvailableWebappUpdateNo(): void {
		$this->managerMocked->shouldReceive('getInstalledWebapp')
			->with(false)->andReturn($this->currentVersion);
		$this->managerMocked->shouldReceive('getCurrentWebapp')
			->with()->andReturn($this->currentVersion);
		Assert::false($this->managerMocked->availableWebappUpdate());
	}

	/**
	 * Tests the function to check if an update is available for the webapp
	 */
	public function testAvailableWebappUpdateYes(): void {
		$this->managerMocked->shouldReceive('getInstalledWebapp')
			->with(false)->andReturn($this->stableVersion);
		$this->managerMocked->shouldReceive('getCurrentWebapp')
			->with()->andReturn($this->currentVersion);
		Assert::true($this->managerMocked->availableWebappUpdate());
	}

	/**
	 * Tests the function to get the current webapp version (stable - success)
	 */
	public function testGetCurrentWebappStable(): void {
		$responseMock = new MockHandler([
			new Response(200, [], '{"version": "' . $this->currentVersion . '"}'),
		]);
		$handler = HandlerStack::create($responseMock);
		$client = new Client(['handler' => $handler]);
		$manager = new VersionManager($this->commandManager, $this->cacheStorage, $client);
		Assert::same($this->currentVersion, $manager->getCurrentWebapp());
	}

	/**
	 * Tests the function to get the current webapp version (stable - failure, master - success)
	 */
	public function testGetCurrentWebappMaster(): void {
		$responseMock = new MockHandler([
			new Response(404),
			new Response(200, [], '{"version": "' . $this->currentVersion . '"}'),
		]);
		$handler = HandlerStack::create($responseMock);
		$client = new Client(['handler' => $handler]);
		$manager = new VersionManager($this->commandManager, $this->cacheStorage, $client);
		Assert::same($this->currentVersion, $manager->getCurrentWebapp());
	}

	/**
	 * Tests the function to get the installed webapp version
	 */
	public function testGetInstalledWebapp(): void {
		Assert::same($this->currentVersion, $this->manager->getInstalledWebapp(false));
	}

	/**
	 * Tests the function to get the installed webapp version with git
	 */
	public function testGetInstalledWebappGit(): void {
		$this->receiveCommand('git rev-parse --is-inside-work-tree', null, 'true');
		$this->receiveCommand('git rev-parse --verify HEAD', null, 'commit');
		$expected = 'v' . $this->currentVersion . ' (commit)';
		Assert::same($expected, $this->manager->getInstalledWebapp());
	}

	/**
	 * Tests the function to get the installed webapp version
	 */
	public function testGetInstalledWebappFallback(): void {
		$this->receiveCommand('git rev-parse --is-inside-work-tree', null, 'false');
		$expected = 'v' . $this->currentVersion;
		Assert::same($expected, $this->manager->getInstalledWebapp());
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->cacheStorage = new DevNullStorage();
		$client = new Client();
		$this->manager = new VersionManager($this->commandManager, $this->cacheStorage, $client);
		$this->managerMocked = Mockery::mock(VersionManager::class)->makePartial();
	}

}

$test = new VersionManagerTest();
$test->run();
