<?php

/**
 * TEST: App\ConfigModule\Models\SchedulerManager
 * @covers App\ConfigModule\Models\SchedulerManager
 * @phpVersion >= 7.3
 * @testCase
 */
/**
 * Copyright 2017-2022 IQRF Tech s.r.o.
 * Copyright 2019-2022 MICRORISC s.r.o.
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

namespace Tests\ConfigModule\Models;

use App\ConfigModule\Exceptions\TaskNotFoundException;
use App\ConfigModule\Models\MainManager;
use App\ConfigModule\Models\SchedulerManager;
use App\ConfigModule\Models\SchedulerSchemaManager;
use App\ConfigModule\Models\TaskTimeManager;
use App\CoreModule\Entities\CommandStack;
use App\CoreModule\Models\CommandManager;
use App\CoreModule\Models\JsonFileManager;
use Mockery;
use stdClass;
use Tester\Assert;
use Tester\Environment;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for scheduler's task configuration manager
 */
final class SchedulerManagerTest extends TestCase {

	/**
	 * @var JsonFileManager JSON file manager
	 */
	private $fileManagerTemp;

	/**
	 * @var SchedulerManager Scheduler's task configuration manager
	 */
	private $manager;

	/**
	 * @var SchedulerManager Scheduler's task configuration manager (with temporary files)
	 */
	private $managerTemp;

	/**
	 * @var stdClass Scheduler's task settings
	 */
	private $array;

	/**
	 * Test function to delete configuration of Scheduler
	 */
	public function testDelete(): void {
		Environment::lock('config_scheduler', TMP_DIR);
		$fileName = '1';
		$this->fileManagerTemp->write($fileName, $this->array);
		Assert::true($this->fileManagerTemp->exists($fileName));
		$this->managerTemp->delete(1);
		Assert::false($this->fileManagerTemp->exists($fileName));
	}

	/**
	 * Test function to delete nonexistent task
	 */
	public function testDeleteNonexistent(): void {
		Assert::throws(function (): void {
			$this->manager->delete(-1);
		}, TaskNotFoundException::class);
	}

	/**
	 * Tests the function to delete all tasks
	 */
	public function testDeleteAll(): void {
		Environment::lock('config_scheduler', TMP_DIR);
		$expected = [];
		$this->managerTemp->deleteAll();
		Assert::same($expected, $this->managerTemp->list());
	}

	/**
	 * Tests function to get task file name
	 */
	public function testGetFileName(): void {
		Assert::same('1', $this->manager->getFileName(1));
	}

	/**
	 * Tests function to get task file name (nonexistent task)
	 */
	public function testGetFileNameNonexistent(): void {
		Assert::throws(function (): void {
			$this->manager->getFileName(-1);
		}, TaskNotFoundException::class);
	}

	/**
	 * Tests function to check task existence
	 */
	public function testExist(): void {
		Assert::true($this->manager->exist(1));
		Assert::false($this->manager->exist(-1));
	}

	/**
	 * Test function to get tasks
	 */
	public function testList(): void {
		$expected = [
			[
				'id' => 1,
				'timeSpec' => (object) [
					'cronTime' => '*/5 * 1 * * * *',
					'exactTime' => false,
					'periodic' => false,
					'period' => 0,
					'startTime' => '',
				],
				'service' => 'SchedulerMessaging',
				'messagings' => 'WebsocketMessaging',
				'mTypes' => 'iqrfRaw',
			], [
				'id' => 2,
				'timeSpec' => (object) [
					'cronTime' => '*/5 * 1 * * * *',
					'exactTime' => false,
					'periodic' => false,
					'period' => 0,
					'startTime' => '',
				],
				'service' => 'SchedulerMessaging',
				'messagings' => 'WebsocketMessaging',
				'mTypes' => 'iqrfRawHdp',
			],
		];
		Assert::equal($expected, $this->manager->list());
	}

	/**
	 * Test function to load configuration of Scheduler
	 */
	public function testLoad(): void {
		$expected = $this->array;
		$expected->timeSpec->cronTime = '*/5 * 1 * * * *';
		Assert::equal($expected, $this->manager->load(1));
	}

	/**
	 * Test function to load nonexistent task
	 */
	public function testLoadNonexistent(): void {
		Assert::throws(function (): void {
			$this->manager->load(-1);
		}, TaskNotFoundException::class);
	}

	/**
	 * Test function to save configuration of Scheduler
	 */
	public function testSave(): void {
		Environment::lock('config_scheduler', TMP_DIR);
		$expected = $this->array;
		$expected->task[0]->message->returnVerbose = false;
		$config = $expected;
		$config->timeSpec->cronTime = '*/5 * 1 * * * *';
		$this->managerTemp->save($config, null);
		Assert::equal($expected, $this->fileManagerTemp->read('1', false));
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->array = (object) [
			'taskId' => 1,
			'clientId' => 'SchedulerMessaging',
			'timeSpec' => (object) [
				'cronTime' => ['*/5', '*', '1', '*', '*', '*', '*'],
				'exactTime' => false,
				'periodic' => false,
				'period' => 0,
				'startTime' => '',
			],
			'task' => [
				(object) [
					'messaging' => 'WebsocketMessaging',
					'message' => (object) [
						'mType' => 'iqrfRaw',
						'data' => (object) [
							'msgId' => '1',
							'timeout' => 1000,
							'req' => (object) ['rData' => '00.00.06.03.ff.ff'],
						],
						'returnVerbose' => true,
					],
				],
			],
		];
	}

	/**
	 * Set up the test environment
	 */
	protected function setUp(): void {
		$configPath = TESTER_DIR . '/data/';
		$configTempPath = TMP_DIR . '/configuration/';
		$commandStack = new CommandStack();
		$commandManager = new CommandManager(false, $commandStack);
		$this->fileManagerTemp = new JsonFileManager($configTempPath . 'scheduler/', $commandManager);
		$mainConfigManager = Mockery::mock(MainManager::class);
		$mainConfigManager->shouldReceive('getCacheDir')->andReturn($configPath);
		$mainConfigManagerTemp = Mockery::mock(MainManager::class);
		$mainConfigManagerTemp->shouldReceive('getCacheDir')->andReturn($configTempPath);
		$timeManager = new TaskTimeManager();
		$schedulerSchemaManager = Mockery::mock(SchedulerSchemaManager::class);
		$schedulerSchemaManager->shouldReceive('validate')
			->andReturnTrue();
		$this->manager = new SchedulerManager($mainConfigManager, $timeManager, $commandManager, $schedulerSchemaManager);
		$this->managerTemp = new SchedulerManager($mainConfigManagerTemp, $timeManager, $commandManager, $schedulerSchemaManager);
	}

	/**
	 * Cleanup the test environment
	 */
	protected function tearDown(): void {
		Mockery::close();
	}

}

$test = new SchedulerManagerTest();
$test->run();
