<?php

/**
 * TEST: App\ConfigModule\Models\SchedulerManager
 * @covers App\ConfigModule\Models\SchedulerManager
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

namespace Tests\ConfigModule\Models;

use App\ConfigModule\Exceptions\TaskNotFoundException;
use App\ConfigModule\Models\GenericManager;
use App\ConfigModule\Models\MainManager;
use App\ConfigModule\Models\SchedulerManager;
use App\ConfigModule\Models\SchedulerSchemaManager;
use App\ConfigModule\Models\TaskTimeManager;
use App\CoreModule\Models\FileManager;
use Iqrf\CommandExecutor\CommandExecutor;
use Iqrf\CommandExecutor\CommandStack;
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
	 * Task UUID
	 */
	private const TASK_UUID = '210735a5-91fb-4ba8-90cf-2dc36251d19b';

	/**
	 * @var FileManager JSON file manager
	 */
	private FileManager $fileManagerTemp;

	/**
	 * @var SchedulerManager Scheduler's task configuration manager
	 */
	private SchedulerManager $manager;

	/**
	 * @var SchedulerManager Scheduler's task configuration manager (with temporary files)
	 */
	private SchedulerManager $managerTemp;

	/**
	 * @var stdClass Scheduler's task settings
	 */
	private readonly stdClass $array;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->array = (object) [
			'taskId' => self::TASK_UUID,
			'clientId' => 'SchedulerMessaging',
			'timeSpec' => (object) [
				'cronTime' => '*/5 * 1 * * * *',
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
	 * Test function to delete configuration of Scheduler
	 */
	public function testDelete(): void {
		Environment::lock('config_scheduler', TMP_DIR);
		$fileName = self::TASK_UUID . '.json';
		$this->fileManagerTemp->writeJson($fileName, $this->array);
		Assert::true($this->fileManagerTemp->exists($fileName));
		$this->managerTemp->delete(self::TASK_UUID);
		Assert::false($this->fileManagerTemp->exists($fileName));
	}

	/**
	 * Test function to delete nonexistent task
	 */
	public function testDeleteNonexistent(): void {
		Assert::throws(function (): void {
			$this->manager->delete('nonexistent');
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
		Assert::same(
			self::TASK_UUID . '.json',
			$this->manager->getFileName(self::TASK_UUID)
		);
	}

	/**
	 * Tests function to get task file name (nonexistent task)
	 */
	public function testGetFileNameNonexistent(): void {
		Assert::throws(function (): void {
			$this->manager->getFileName('nonexistent');
		}, TaskNotFoundException::class);
	}

	/**
	 * Tests function to check task existence
	 */
	public function testExist(): void {
		Assert::true($this->manager->exist(self::TASK_UUID));
		Assert::false($this->manager->exist('nonexistent'));
	}

	/**
	 * Test function to get tasks
	 */
	public function testList(): void {
		$expected = [
			(object) [
				'clientId' => 'SchedulerMessaging',
				'taskId' => self::TASK_UUID,
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
				'timeSpec' => (object) [
					'cronTime' => '*/5 * 1 * * * *',
					'exactTime' => false,
					'periodic' => false,
					'period' => 0,
					'startTime' => '',
				],
			],
			(object) [
				'clientId' => 'SchedulerMessaging',
				'taskId' => '813db500-95ad-40e4-9bb3-8b8f8cd7f629',
				'task' => [
					(object) [
						'messaging' => 'WebsocketMessaging',
						'message' => (object) [
							'mType' => 'iqrfRawHdp',
							'timeout' => 1000,
							'data' => (object) [
								'msgId' => '123',
								'req' => (object) [
									'nAdr' => 0,
									'pNum' => 6,
									'pCmd' => 3,
									'hwpId' => 65535,
									'pData' => [],
								],
								'returnVerbose' => true,
							],
						],
					],
				],
				'timeSpec' => (object) [
					'cronTime' => ['*/5', '*', '1', '*', '*', '*', '*'],
					'exactTime' => false,
					'periodic' => false,
					'period' => 0,
					'startTime' => '',
				],
			],
		];
		Assert::equal($expected, $this->manager->list());
	}

	/**
	 * Test function to load configuration of Scheduler
	 */
	public function testLoad(): void {
		$expected = $this->array;
		Assert::equal($expected, $this->manager->load(self::TASK_UUID));
	}

	/**
	 * Test function to load nonexistent task
	 */
	public function testLoadNonexistent(): void {
		Assert::throws(function (): void {
			$this->manager->load('nonexistent');
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
		Assert::equal($expected, $this->fileManagerTemp->readJson('210735a5-91fb-4ba8-90cf-2dc36251d19b.json', false));
	}

	/**
	 * Set up the test environment
	 */
	protected function setUp(): void {
		$configPath = TESTER_DIR . '/data/';
		$configTempPath = TMP_DIR . '/configuration/';
		$commandStack = new CommandStack();
		$commandManager = new CommandExecutor(false, $commandStack);
		$this->fileManagerTemp = new FileManager($configTempPath . 'scheduler/', $commandManager);
		$mainConfigManager = Mockery::mock(MainManager::class);
		$mainConfigManager->shouldReceive('getCacheDir')->andReturn($configPath);
		$mainConfigManagerTemp = Mockery::mock(MainManager::class);
		$mainConfigManagerTemp->shouldReceive('getCacheDir')->andReturn($configTempPath);
		$timeManager = new TaskTimeManager();
		$schedulerSchemaManager = Mockery::mock(SchedulerSchemaManager::class);
		$schedulerSchemaManager->shouldReceive('validate')
			->andReturnTrue();
		$genericManager = Mockery::mock(GenericManager::class);
		$this->manager = new SchedulerManager($mainConfigManager, $timeManager, $commandManager, $schedulerSchemaManager, $genericManager);
		$this->managerTemp = new SchedulerManager($mainConfigManagerTemp, $timeManager, $commandManager, $schedulerSchemaManager, $genericManager);
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
