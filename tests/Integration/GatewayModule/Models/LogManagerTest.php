<?php

/**
 * TEST: App\GatewayModule\Models\LogManager
 * @covers App\GatewayModule\Models\LogManager
 * @phpVersion >= 7.3
 * @testCase
 */
/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
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

use App\CoreModule\Entities\CommandStack;
use App\CoreModule\Models\CommandManager;
use App\CoreModule\Models\FileManager;
use App\CoreModule\Models\ZipArchiveManager;
use App\GatewayModule\Exceptions\LogNotFoundException;
use App\GatewayModule\Exceptions\ServiceLogNotAvailableException;
use App\GatewayModule\Models\LogManager;
use Tester\Assert;
use Tests\Stubs\CoreModule\Models\Command;
use Tests\Toolkit\TestCases\CommandTestCase;
use ZipArchive;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for Gateway info manager
 */
final class LogManagerTest extends CommandTestCase {

	/**
	 * Command manager commands
	 */
	private const COMMANDS = [
		'journal' => 'journalctl --utc -b --no-pager',
	];

	/**
	 * @var FileManager Text file manager
	 */
	private $fileManager;

	/**
	 * @var LogManager IQRF Gateway Daemon's log manager
	 */
	private $manager;

	/**
	 * @var LogManager Log manager with mocked command manager
	 */
	private $managerMockCommand;

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
			'iqrf-gateway-controller',
			'iqrf-gateway-daemon',
			'iqrf-gateway-uploader',
			'systemd-journald',
		];
		$this->commandManager->shouldReceive('commandExist')
			->withArgs(['iqrf-gateway-controller'])
			->andReturn(true);
		$this->commandManager->shouldReceive('commandExist')
			->withArgs(['iqrfgd2'])
			->andReturn(true);
		$this->commandManager->shouldReceive('commandExist')
			->withArgs(['iqrf-gateway-uploader'])
			->andReturn(true);
		Assert::same($expected, $this->managerMockCommand->getAvailableServices());
	}

	/**
	 * Tests the function to get service log
	 */
	public function testGetServiceLog(): void {
		$this->commandManager->shouldReceive('commandExist')
			->withArgs(['iqrf-gateway-controller'])
			->andReturn(true);
		$this->commandManager->shouldReceive('commandExist')
			->withArgs(['iqrfgd2'])
			->andReturn(true);
		$this->commandManager->shouldReceive('commandExist')
			->withArgs(['iqrf-gateway-uploader'])
			->andReturn(true);
		$expected = $this->fileManager->read('iqrf-gateway-controller.log');
		Assert::same($expected, $this->managerMockCommand->getServiceLog('iqrf-gateway-controller'));
		$expected = $this->fileManager->read('daemon/2018-08-13-13-37-834-iqrf-gateway-daemon.log');
		Assert::same($expected, $this->managerMockCommand->getServiceLog('iqrf-gateway-daemon'));
		$expected = $this->fileManager->read('iqrf-gateway-uploader.log');
		Assert::same($expected, $this->managerMockCommand->getServiceLog('iqrf-gateway-uploader'));
		$expected = $this->fileManager->read('journal.log');
		$command = new Command(self::COMMANDS['journal'], $expected, '', 0);
		$this->commandManager->shouldReceive('run')
			->withArgs([self::COMMANDS['journal'], true])
			->andReturn($command);
		Assert::same($expected, $this->managerMockCommand->getServiceLog('systemd-journald'));
	}

	/**
	 * Tests the function to get log of nonexistent service
	 */
	public function testGetServiceLogNonexistent(): void {
		$this->commandManager->shouldReceive('commandExist')
			->withArgs(['iqrf-gateway-controller'])
			->andReturn(true);
		$this->commandManager->shouldReceive('commandExist')
			->withArgs(['iqrfgd2'])
			->andReturn(true);
		$this->commandManager->shouldReceive('commandExist')
			->withArgs(['iqrf-gateway-uploader'])
			->andReturn(true);
		Assert::exception(function (): void {
			$this->managerMockCommand->getServiceLog('nonexistent');
		}, ServiceLogNotAvailableException::class);
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$logDir = realpath(__DIR__ . '/../../../data/logs/');
		$commandStack = new CommandStack();
		$commandManager = new CommandManager(false, $commandStack);
		$this->fileManager = new FileManager($logDir, $commandManager);
		$this->manager = new LogManager($logDir . '/', $logDir . '/daemon/', $commandManager);
		$this->managerMockCommand = new LogManager($logDir . '/', $logDir . '/daemon/', $this->commandManager);
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
