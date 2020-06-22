<?php

/**
 * TEST: App\CoreModule\Models\VersionManager
 * @covers App\CoreModule\Models\VersionManager
 * @phpVersion >= 7.1
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Unit\CoreModule\Models;

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

require __DIR__ . '/../../../bootstrap.php';

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
	private $currentVersion = '2.1.0-alpha';

	/**
	 * @var string Current stable version of the webapp
	 */
	private $stableVersion = '2.0.0';

	/**
	 * @var Mock|VersionManager Version manager
	 */
	private $manager;

	/**
	 * Tests the function to check if an update is available for the webapp
	 */
	public function testAvailableWebappUpdateNo(): void {
		$this->manager->shouldReceive('getInstalledWebapp')
			->withArgs([false])->andReturn($this->currentVersion);
		$this->manager->shouldReceive('getCurrentWebapp')
			->withArgs([])->andReturn($this->currentVersion);
		Assert::false($this->manager->availableWebappUpdate());
	}

	/**
	 * Tests the function to check if an update is available for the webapp
	 */
	public function testAvailableWebappUpdateYes(): void {
		$this->manager->shouldReceive('getInstalledWebapp')
			->withArgs([false])->andReturn($this->stableVersion);
		$this->manager->shouldReceive('getCurrentWebapp')
			->withArgs([])->andReturn($this->currentVersion);
		Assert::true($this->manager->availableWebappUpdate());
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
		$this->manager = Mockery::mock(VersionManager::class, [$this->commandManager, $this->cacheStorage, $client])->makePartial();
	}

}

$test = new VersionManagerTest();
$test->run();
