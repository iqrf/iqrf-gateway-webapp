<?php

/**
 * TEST: App\MaintenanceModule\Models\MonitManager
 * @covers App\MaintenanceModule\Models\MonitManager
 * @phpVersion >= 7.4
 * @testCase
 */
/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
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

use App\CoreModule\Entities\CommandStack;
use App\CoreModule\Models\CommandManager;
use App\CoreModule\Models\FileManager;
use App\MaintenanceModule\Exceptions\MonitConfigErrorException;
use App\MaintenanceModule\Models\MonitManager;
use Nette\Utils\FileSystem;
use Tester\Assert;
use Tester\Environment;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for Monit manager
 */
final class MonitManagerTest extends TestCase {

	/**
	 * @var string Monit configuration file name
	 */
	private const FILE_NAME = 'monitrc';

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
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		Environment::lock('monit', TMP_DIR);
		$monitDir = realpath(TESTER_DIR . '/data/maintenance/');
		$monitTempDir = realpath(TMP_DIR);
		FileSystem::copy($monitDir, $monitTempDir . '/maintenance/');
		$commandStack = new CommandStack();
		$commandManager = new CommandManager(false, $commandStack);
		$this->fileManager = new FileManager($monitDir, $commandManager);
		$this->fileManagerTemp = new FileManager($monitTempDir . '/maintenance/', $commandManager);
		$this->manager = new MonitManager($this->fileManager);
		$this->managerTemp = new MonitManager($this->fileManagerTemp);
	}

	/**
	 * Tests the function to read monit configuration file
	 */
	public function testReadConfig(): void {
		$expected = $this->fileManager->read(self::FILE_NAME);
		Assert::same($expected, $this->manager->readConfig());
	}

	/**
	 * Tests the function to get monit configuration
	 */
	public function testGetConfig(): void {
		$expected = [
			'endpoint' => 'testendpoint',
			'username' => 'testuser',
			'password' => 'testpass',
		];
		Assert::same($expected, $this->manager->getConfig());
	}

	/**
	 * Tests the function to get monit configuration from invalid file
	 */
	public function testGetConfigInvalid(): void {
		$this->fileManagerTemp->write(self::FILE_NAME, 'Invalid content.');
		Assert::exception(function (): void {
			$this->managerTemp->getConfig();
		}, MonitConfigErrorException::class, 'Monit configuration file contains invalid content.');
	}

	/**
	 * Tests the function to save monit configuration
	 */
	public function testSaveConfig(): void {
		$expected = [
			'endpoint' => 'nonexistentdomain.org/collector',
			'username' => 'username',
			'password' => 'password',
		];
		$this->managerTemp->saveConfig($expected);
		Assert::same($expected, $this->managerTemp->getConfig());
	}

	/**
	 * Tests the function to save monit configuration to invalid file
	 */
	public function testSaveConfigInvalid(): void {
		$this->fileManagerTemp->write(self::FILE_NAME, 'Invalid content');
		Assert::exception(function (): void {
			$config = [
				'endpoint' => 'nonexistentdomain.org/collector',
				'username' => 'username',
				'password' => 'password',
			];
			$this->managerTemp->saveConfig($config);
		}, MonitConfigErrorException::class, 'Monit configuration file contains invalid content.');
	}

}

$test = new MonitManagerTest();
$test->run();
