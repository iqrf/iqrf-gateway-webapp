<?php

/**
 * TEST: App\GatewayModule\Models\LogManager
 * @covers App\GatewayModule\Models\LogManager
 * @phpVersion >= 7.4
 * @testCase
 */
/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
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

namespace Tests\Integration\GatewayModule\Models;

use App\CoreModule\Models\FileManager;
use App\CoreModule\Models\ZipArchiveManager;
use App\GatewayModule\Exceptions\LogNotFoundException;
use App\GatewayModule\Exceptions\ServiceLogNotAvailableException;
use App\GatewayModule\Models\LogManager;
use Iqrf\CommandExecutor\CommandExecutor;
use Iqrf\CommandExecutor\CommandStack;
use Iqrf\CommandExecutor\Tester\Traits\CommandExecutorTestCase;
use Tester\Assert;
use Tester\TestCase;
use ZipArchive;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for Gateway info manager
 */
final class LogManagerTest extends TestCase {

	use CommandExecutorTestCase;

	/**
	 * @var FileManager Text file manager
	 */
	private FileManager $fileManager;

	/**
	 * @var LogManager IQRF Gateway Daemon's log manager
	 */
	private LogManager $manager;

	/**
	 * @var LogManager Log manager with mocked command manager
	 */
	private LogManager $managerMockCommand;

	/**
	 * Tests the function to get the latest IQRF Gateway Daemon's log
	 */
	public function testGetLatestDaemonLog(): void {
		$fileName = '2018-08-13-13-37-834-iqrf-gateway-daemon.log';
		$expected = $this->fileManager->read('daemon/' . $fileName);
		Assert::same($expected, $this->manager->getLatestDaemonLog());
	}

	/**
	 * Tests the function to get log from path
	 */
	public function testGetLogFromPath(): void {
		$fileName = 'iqrf-gateway-controller.log';
		$expected = $this->fileManager->read('iqrf-gateway-controller.log');
		Assert::same($expected, $this->manager->getLogFromPath($fileName));
	}

	/**
	 * Tests the function to get log from path if the file does not exist
	 */
	public function testGetLogFromPathNonexistent(): void {
		Assert::exception(function (): void {
			$this->manager->getLogFromPath('nonexistent.log');
		}, LogNotFoundException::class);
	}

	/**
	 * Tests the function to create a ZIP archive with IQRF Gateway Daemon's logs
	 */
	public function testCreateArchive(): void {
		$actual = $this->manager->createArchive();
		$expected = '/tmp/iqrf-gateway-logs.zip';
		Assert::equal($expected, $actual);
		$zipManager = new ZipArchiveManager($expected, ZipArchive::CREATE);
		$logs = [
			'daemon/2018-08-13-13-37-834-iqrf-gateway-daemon.log',
			'daemon/2018-08-13-13-37-496-iqrf-gateway-daemon.log',
		];
		foreach ($logs as $log) {
			$expectedLog = $this->fileManager->read($log);
			Assert::same($expectedLog, $zipManager->openFile($log));
		}
	}

	/**
	 * Tests the function to get available services
	 */
	public function testGetAvailableServices(): void {
		$expected = [
			LogManager::CONTROLLER,
			LogManager::DAEMON,
			LogManager::SETTER,
			LogManager::TRANSLATOR,
			LogManager::UPLOADER,
		];
		$this->receiveCommandExist(LogManager::CONTROLLER, true);
		$this->receiveCommandExist('iqrfgd2', true);
		$this->receiveCommandExist(LogManager::SETTER, true);
		$this->receiveCommandExist(LogManager::TRANSLATOR, true);
		$this->receiveCommandExist(LogManager::UPLOADER, true);
		Assert::same($expected, $this->managerMockCommand->getAvailableServices());
	}

	/**
	 * Tests the function to get service log
	 */
	public function testGetServiceLog(): void {
		$expected = $this->fileManager->read('iqrf-gateway-controller.log');
		Assert::same($expected, $this->managerMockCommand->getServiceLog(LogManager::CONTROLLER));
		$expected = $this->fileManager->read('daemon/2018-08-13-13-37-834-iqrf-gateway-daemon.log');
		Assert::same($expected, $this->managerMockCommand->getServiceLog(LogManager::DAEMON));
		$expected = $this->fileManager->read('iqrf-gateway-uploader.log');
		Assert::same($expected, $this->managerMockCommand->getServiceLog(LogManager::UPLOADER));
	}

	/**
	 * Tests the function to get log of nonexistent service
	 */
	public function testGetServiceLogNonexistent(): void {
		Assert::exception(function (): void {
			$this->managerMockCommand->getServiceLog('nonexistent');
		}, ServiceLogNotAvailableException::class);
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->setUpCommandExecutor();
		$logDir = realpath(TESTER_DIR . '/data/logs/');
		$commandStack = new CommandStack();
		$commandManager = new CommandExecutor(false, $commandStack);
		$this->fileManager = new FileManager($logDir, $commandManager);
		$this->manager = new LogManager($logDir . '/', $logDir . '/daemon/', $commandManager);
		$this->managerMockCommand = new LogManager($logDir . '/', $logDir . '/daemon/', $this->commandExecutor);
		$modifyDates = [
			'2018-08-13T13:37:13.107090' => 'daemon/2018-08-13-13-37-496-iqrf-gateway-daemon.log',
			'2018-08-13T13:37:18.262028' => 'daemon/2018-08-13-13-37-834-iqrf-gateway-daemon.log',
		];
		foreach ($modifyDates as $date => $fileName) {
			$commandManager->run('touch -m -d ' . $date . ' ' . $logDir . '/' . $fileName);
		}
	}

}

$test = new LogManagerTest();
$test->run();
