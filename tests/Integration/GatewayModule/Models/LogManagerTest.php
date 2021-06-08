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
use App\GatewayModule\Models\LogManager;
use Tester\Assert;
use Tester\TestCase;
use ZipArchive;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for Gateway info manager
 */
final class LogManagerTest extends TestCase {

	/**
	 * @var FileManager Text file manager
	 */
	private $fileManager;

	/**
	 * @var LogManager IQRF Gateway Daemon's log manager
	 */
	private $manager;

	/**
	 * Tests the function to load the latest IQRF Gateway Daemon's log
	 */
	public function testLoad(): void {
		$fileName = '2018-08-13-13-37-834-iqrf-gateway-daemon.log';
		$expected = [
			'daemon' => $this->fileManager->read('daemon/' . $fileName),
		];
		Assert::same($expected, $this->manager->load());
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
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$logDir = realpath(__DIR__ . '/../../../data/logs/');
		$commandStack = new CommandStack();
		$commandManager = new CommandManager(false, $commandStack);
		$this->fileManager = new FileManager($logDir, $commandManager);
		$this->manager = new LogManager($logDir . '/', $logDir . '/daemon/', $commandManager);
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
