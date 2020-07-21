<?php

/**
 * TEST: App\ConfigModule\Models\SchedulerManager
 * @covers App\ConfigModule\Models\SchedulerManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\ConfigModule\Models;

use App\ConfigModule\Exceptions\TaskNotFoundException;
use App\ConfigModule\Models\ComponentSchemaManager;
use App\ConfigModule\Models\GenericManager;
use App\ConfigModule\Models\MainManager;
use App\ConfigModule\Models\SchedulerManager;
use App\ConfigModule\Models\SchedulerSchemaManager;
use App\ConfigModule\Models\TaskTimeManager;
use App\CoreModule\Entities\CommandStack;
use App\CoreModule\Models\CommandManager;
use App\CoreModule\Models\JsonFileManager;
use App\ServiceModule\Models\ServiceManager;
use Mockery;
use Mockery\MockInterface;
use stdClass;
use Tester\Assert;
use Tester\Environment;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for scheduler's task configuration manager
 */
class SchedulerManagerTest extends TestCase {

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
	 * @var ServiceManager|MockInterface IQRF Gateway Daemon's service manager
	 */
	private $serviceManager;

	/**
	 * @var TaskTimeManager Scheduler's task time specification manager
	 */
	private $timeManager;

	/**
	 * @var stdClass Scheduler's task settings
	 */
	private $array;

	/**
	 * Test function to delete configuration of Scheduler
	 */
	public function testDelete(): void {
		Environment::lock('config_scheduler', __DIR__ . '/../../temp/');
		$fileName = '1';
		$this->serviceManager->shouldReceive('restart');
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
	 * Test function to get available messagings
	 */
	public function testGetMessagings(): void {
		$expected = [
			'config.mq.title' => ['MqMessaging'],
			'config.mqtt.title' => ['MqttMessaging'],
			'config.websocket.title' => ['WebsocketMessaging'],
		];
		Assert::same($expected, $this->manager->getMessagings());
	}

	/**
	 * Test function to get scheduler's services
	 */
	public function testGetServices(): void {
		$expected = ['SchedulerMessaging'];
		Assert::same($expected, $this->manager->getServices());
	}

	/**
	 * Test function to get tasks
	 */
	public function testList(): void {
		$expected = [
			[
				'time' => '*/5 * 1 * * * *',
				'service' => 'SchedulerMessaging',
				'messagings' => 'WebsocketMessaging',
				'mTypes' => 'iqrfRaw',
				'id' => 1,
			], [
				'time' => '*/5 * 1 * * * *',
				'service' => 'SchedulerMessaging',
				'messagings' => 'WebsocketMessaging',
				'mTypes' => 'iqrfRawHdp',
				'id' => 2,
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
		Environment::lock('config_scheduler', __DIR__ . '/../../temp/');
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
		$configPath = __DIR__ . '/../../data/';
		$configTempPath = __DIR__ . '/../../temp/configuration/';
		$schemaPath = __DIR__ . '/../../data/cfgSchemas/';
		$commandStack = new CommandStack();
		$commandManager = new CommandManager(false, $commandStack);
		$this->fileManagerTemp = new JsonFileManager($configTempPath . 'scheduler/', $commandManager);
		$fileManager = new JsonFileManager($configPath, $commandManager);
		$schemaManager = new ComponentSchemaManager($schemaPath, $commandManager);
		$genericConfigManager = new GenericManager($fileManager, $schemaManager);
		$mainConfigManager = Mockery::mock(MainManager::class);
		$mainConfigManager->shouldReceive('getCacheDir')->andReturn($configPath);
		$mainConfigManagerTemp = Mockery::mock(MainManager::class);
		$mainConfigManagerTemp->shouldReceive('getCacheDir')->andReturn($configTempPath);
		$this->timeManager = new TaskTimeManager();
		$this->serviceManager = Mockery::mock(ServiceManager::class);
		$schedulerSchemaManager = Mockery::mock(SchedulerSchemaManager::class);
		$schedulerSchemaManager->shouldReceive('validate')
			->andReturnTrue();
		$this->manager = new SchedulerManager($mainConfigManager, $genericConfigManager, $this->timeManager, $this->serviceManager, $commandManager, $schedulerSchemaManager);
		$this->managerTemp = new SchedulerManager($mainConfigManagerTemp, $genericConfigManager, $this->timeManager, $this->serviceManager, $commandManager, $schedulerSchemaManager);
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
