<?php

/**
 * TEST: App\ConfigModule\Models\MainManager
 * @covers App\ConfigModule\Models\MainManager
 * @phpVersion >= 7.4
 * @testCase
 */
/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
declare(strict_types = 1);

namespace Tests\Integration\ConfigModule\Models;

use App\ConfigModule\Models\MainManager;
use App\CoreModule\Models\FileManager;
use Mockery;
use Nette\IOException;
use Tester\Assert;
use Tester\Environment;
use Tests\Toolkit\TestCases\JsonConfigTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for main configuration manager
 */
final class MainManagerTest extends JsonConfigTestCase {

	/**
	 * File name
	 */
	private const FILE_NAME = 'config.json';

	/**
	 * @var MainManager Main configuration manager
	 */
	private MainManager $manager;

	/**
	 * Tests the function to get cache directory (failure)
	 */
	public function testGetCacheDirFailure(): void {
		$fileManager = Mockery::mock(FileManager::class);
		$fileManager->shouldReceive('readJson')
			->withArgs([self::FILE_NAME])
			->andThrows(IOException::class);
		$manager = new MainManager($fileManager);
		$expected = '/var/cache/iqrf-gateway-daemon/';
		Assert::same($expected, $manager->getCacheDir());
	}

	/**
	 * Tests the function to get cache directory (success)
	 */
	public function testGetCacheDirSuccess(): void {
		$expected = '/var/cache/iqrf-gateway-daemon/';
		Assert::same($expected, $this->manager->getCacheDir());
	}

	/**
	 * Tests the function to get data directory (failure)
	 */
	public function testGetDataDirFailure(): void {
		$fileManager = Mockery::mock(FileManager::class);
		$fileManager->shouldReceive('readJson')
			->withArgs([self::FILE_NAME])
			->andThrows(IOException::class);
		$manager = new MainManager($fileManager);
		$expected = '/usr/share/iqrf-gateway-daemon/';
		Assert::same($expected, $manager->getDataDir());
	}

	/**
	 * Tests the function to get data directory (success)
	 */
	public function testGetDataDirSuccess(): void {
		$expected = '/usr/share/iqrf-gateway-daemon/';
		Assert::same($expected, $this->manager->getDataDir());
	}

	/**
	 * Tests the function to load main configuration of daemon
	 */
	public function testLoad(): void {
		$expected = $this->readFile(self::FILE_NAME);
		Assert::equal($expected, $this->manager->load());
	}

	/**
	 * Tests the function to save main configuration of daemon
	 */
	public function testSave(): void {
		Environment::lock('config_main', TMP_DIR);
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
		$expected = $this->readFile(self::FILE_NAME);
		$this->copyFile(self::FILE_NAME);
		$expected['configurationDir'] = '/etc/iqrf-daemon';
		$manager->save($array);
		Assert::equal($expected, $this->readTempFile(self::FILE_NAME));
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
