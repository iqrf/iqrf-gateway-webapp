<?php

/**
 * TEST: App\MaintenanceModule\Models\MonitManager
 * @covers App\MaintenanceModule\Models\MonitManager
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

namespace Tests\Integration\MaintenanceModule\Models;

use App\MaintenanceModule\Exceptions\MonitConfigErrorException;
use App\MaintenanceModule\Models\MonitManager;
use Iqrf\CommandExecutor\Tester\Traits\CommandExecutorTestCase;
use Iqrf\FileManager\FileManager;
use Nette\Utils\FileSystem;
use Tester\Assert;
use Tester\Environment;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for Monit manager
 */
final class MonitManagerTest extends TestCase {

	use CommandExecutorTestCase;

	/**
	 * @var FileManager Text file manager
	 */
	private FileManager $fileManager;

	/**
	 * @var FileManager Text file manager temp
	 */
	private FileManager $fileManagerTemp;

	/**
	 * @var MonitManager Monit manager
	 */
	private MonitManager $manager;

	/**
	 * @var MonitManager Monit manager
	 */
	private MonitManager $managerTemp;

	/**
	 * Tests the function to get monit configuration
	 */
	public function testGetConfig(): void {
		$expected = [
			'checks' => [
				[
					'name' => 'system',
					'enabled' => false,
				],
			],
			'mmonit' => [
				'enabled' => false,
				'credentials' => [
					'username' => 'testuser',
					'password' => 'testpass',
				],
				'server' => 'https://testendpoint',
			],
		];
		Assert::same($expected, $this->manager->getConfig());
	}

	/**
	 * Tests the function to get monit configuration from monitrc file
	 */
	public function testGetConfigMigrated(): void {
		$this->fileManagerTemp->delete('conf-available/mmonit');
		$expected = [
			'checks' => [
				[
					'name' => 'system',
					'enabled' => false,
				],
			],
			'mmonit' => [
				'enabled' => false,
				'credentials' => [
					'username' => 'testuser',
					'password' => 'testpass',
				],
				'server' => 'https://testendpoint',
			],
		];
		Assert::same($expected, $this->managerTemp->getConfig());
	}

	/**
	 * Tests the function to get monit configuration from invalid file
	 */
	public function testGetConfigInvalid(): void {
		$this->fileManagerTemp->write('conf-available/mmonit', 'Invalid content.');
		Assert::exception(function (): void {
			$this->managerTemp->getConfig();
		}, MonitConfigErrorException::class, 'Monit configuration file contains invalid content.');
	}

	/**
	 * Tests the function to save monit configuration
	 */
	public function testSaveConfig(): void {
		$realPath = realpath($this->fileManagerTemp->getBasePath());
		$this->receiveCommand(
			command: 'monit -t -c ' . escapeshellarg($realPath . '/conf-available/check_system'),
			needSudo: true,
		);
		$this->receiveCommand(
			command: 'monit -t -c ' . escapeshellarg($realPath . '/conf-available/mmonit'),
			needSudo: true,
			count: 2,
		);
		$expected = [
			'checks' => [
				[
					'name' => 'system',
					'enabled' => true,
				],
			],
			'mmonit' => [
				'enabled' => true,
				'credentials' => [
					'username' => 'u$er',
					'password' => 'p@ss',
				],
				'server' => 'http://company:8080/monit',
			],
		];
		$this->managerTemp->saveConfig($expected);
		Assert::same($expected, $this->managerTemp->getConfig());
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		Environment::lock('monit', TMP_DIR);
		parent::setUp();
		$this->setUpCommandExecutor();
		$monitDir = realpath(TESTER_DIR . '/data/maintenance/monit/');
		$monitTempDir = realpath(TMP_DIR) . '/maintenance/monit/';
		$filesToChmod = [
			'' => 777,
			'/conf-available/check_system' => 666,
			'/conf-available/mmonit' => 666,
			'/conf-enabled/check_system' => 666,
			'/conf-enabled/mmonit' => 666,
		];
		foreach ([$monitDir, $monitTempDir] as $dir) {
			foreach ($filesToChmod as $file => $mode) {
				$this->receiveCommand(
					command: 'chmod ' . $mode . ' ' . escapeshellarg($dir . $file),
					needSudo: true,
					count: null,
				);
			}
		}
		FileSystem::copy($monitDir, $monitTempDir);
		$this->fileManager = new FileManager($monitDir, $this->commandExecutor);
		$this->fileManagerTemp = new FileManager($monitTempDir, $this->commandExecutor);
		$this->manager = new MonitManager($this->fileManager, $this->commandExecutor);
		$this->managerTemp = new MonitManager($this->fileManagerTemp, $this->commandExecutor);
	}

}

$test = new MonitManagerTest();
$test->run();
