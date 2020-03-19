<?php

/**
 * TEST: App\ConfigModule\Models\MainManager
 * @covers App\ConfigModule\Models\MainManager
 * @phpVersion >= 7.1
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Integration\ConfigModule\Models;

use App\ConfigModule\Models\MainManager;
use App\CoreModule\Models\JsonFileManager;
use Mockery;
use Nette\IOException;
use Tester\Assert;
use Tester\Environment;
use Tests\Toolkit\TestCases\JsonConfigTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for main configuration manager
 */
class MainManagerTest extends JsonConfigTestCase {

	/**
	 * @var string File name (without .json)
	 */
	private $fileName = 'config';

	/**
	 * @var MainManager Main configuration manager
	 */
	private $manager;

	/**
	 * Tests the function to get cache directory (failure)
	 */
	public function testGetCacheDirFailure(): void {
		$fileManager = Mockery::mock(JsonFileManager::class);
		$fileManager->shouldReceive('read')
			->withArgs([$this->fileName])
			->andThrows(IOException::class);
		$manager = new MainManager($fileManager);
		$expected = '/var/cache/iqrf-gateway-daemon/';
		Assert::same($expected, $manager->getCacheDir());
	}

	/**
	 * Tests the function to get cache directory (success)
	 */
	public function testGetCacheDirSuccess(): void {
		$expected = '/var/cache/iqrf-gateway-daemon';
		Assert::same($expected, $this->manager->getCacheDir());
	}

	/**
	 * Tests the function to load main configuration of daemon
	 */
	public function testLoad(): void {
		$expected = $this->readFile($this->fileName);
		Assert::equal($expected, $this->manager->load());
	}

	/**
	 * Tests the function to save main configuration of daemon
	 */
	public function testSave(): void {
		Environment::lock('config_main', __DIR__ . '/../../../temp/');
		$manager = new MainManager($this->fileManagerTemp);
		$array = [
			'applicationName' => 'IqrfGatewayDaemon',
			'resourceDir' => '',
			'dataDir' => '/usr/share/iqrf-gateway-daemon',
			'cacheDir' => '/var/cache/iqrf-gateway-daemon',
			'userDir' => '',
			'configurationDir' => '/etc/iqrf-daemon',
			'deploymentDir' => '/usr/lib/iqrf-gateway-daemon',
		];
		$expected = $this->readFile($this->fileName);
		$this->copyFile($this->fileName);
		$expected['configurationDir'] = '/etc/iqrf-daemon';
		$manager->save($array);
		Assert::equal($expected, $this->readTempFile($this->fileName));
	}

	/**
	 * Sets up the testing environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->manager = new MainManager($this->fileManager);
	}

}

$test = new MainManagerTest();
$test->run();
