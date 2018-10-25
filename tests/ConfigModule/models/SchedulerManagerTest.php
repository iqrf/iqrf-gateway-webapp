<?php

/**
 * TEST: App\ConfigModule\Models\SchedulerManager
 * @covers App\ConfigModule\Models\SchedulerManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\ConfigModule\Models;

use App\ConfigModule\Models\GenericManager;
use App\ConfigModule\Models\MainManager;
use App\ConfigModule\Models\SchedulerManager;
use App\CoreModule\Models\JsonFileManager;
use App\CoreModule\Models\JsonSchemaManager;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for scheduler's task configuration manager
 */
class SchedulerManagerTest extends TestCase {

	/**
	 * @var Container Nette Tester Container
	 */
	private $container;

	/**
	 * @var JsonFileManager JSON file manager
	 */
	private $fileManager;

	/**
	 * @var JsonFileManager JSON file manager
	 */
	private $fileManagerTemp;

	/**
	 * @var SchedulerManager Scheduler's task configuration manager
	 */
	private $manager;

	/**
	 * @var array Scheduler's task settings
	 */
	private $array = [
		'time' => '*/5 * 1 * * * *',
		'service' => 'SchedulerMessaging',
		'task' => [
			'messaging' => 'WebsocketMessaging',
			'message' => [
				'mType' => 'iqrfRaw',
				'data' => [
					'msgId' => '1',
					'timeout' => 1000,
					'req' => ['rData' => '00.00.06.03.ff.ff'],
				],
				'returnVerbose' => true,
			],],
	];

	/**
	 * @var string File name (without .json)
	 */
	private $fileName = 'Tasks';

	/**
	 * Constructor
	 * @param Container $container Nette Tester Container
	 */
	public function __construct(Container $container) {
		$this->container = $container;
	}

	/**
	 * Test function to add configuration of Scheduler
	 */
	public function testAdd(): void {
		\Tester\Environment::lock('config_scheduler', __DIR__ . '/../../temp/');
		$expected = $this->fileManager->read($this->fileName);
		$this->fileManagerTemp->write($this->fileName, $expected);
		$this->manager->add('raw');
		$task = [
			'time' => '',
			'service' => '',
			'task' => [
				'messaging' => '',
				'message' => [
					'mType' => 'iqrfRaw',
					'data' => [
						'msgId' => '',
						'timeout' => 0,
						'req' => ['rData' => ''],
					],
					'returnVerbose' => true,
				],],
		];
		array_push($expected['TasksJson'], $task);
		Assert::equal($expected, $this->fileManagerTemp->read($this->fileName));
	}

	/**
	 * Test function to delete configuration of Scheduler
	 */
	public function testDelete(): void {
		\Tester\Environment::lock('config_scheduler', __DIR__ . '/../../temp/');
		$expected = $this->fileManager->read($this->fileName);
		$this->fileManagerTemp->write($this->fileName, $expected);
		unset($expected['TasksJson'][5]);
		$this->manager->delete(5);
		Assert::equal($expected, $this->fileManagerTemp->read($this->fileName));
	}

	/**
	 * Test function to fix HWPID format
	 */
	public function testFixHwpid(): void {
		Assert::equal('01.00', $this->manager->fixHwpid(1));
	}

	/**
	 * Test function to fix HWPID format (empty)
	 */
	public function testFixHwpidEmpty(): void {
		Assert::equal('00.00', $this->manager->fixHwpid());
	}

	/**
	 * Test function to get last ID
	 */
	public function testGetLastId(): void {
		$expected = count($this->fileManager->read($this->fileName)['TasksJson']) - 1;
		Assert::equal($expected, $this->manager->getLastId());
	}

	/**
	 * Test function to get avaiable messagings
	 */
	public function testGetMessagings(): void {
		$expected = [
			'config.mq.title' => ['MqMessaging',],
			'config.mqtt.title' => ['MqttMessaging',],
			'config.udp.title' => ['UdpMessaging',],
			'config.websocket.title' => [
				'WebsocketMessaging', 'WebsocketMessagingMobileApp',
				'WebsocketMessagingWebApp',
			],
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
				'messaging' => 'WebsocketMessaging',
				'mType' => 'iqrfRaw',
				'request' => '00.00.06.03.ff.ff',
				'id' => 0,
			], [
				'time' => '*/5 * 1 * * * *',
				'service' => 'SchedulerMessaging',
				'messaging' => 'WebsocketMessaging',
				'mType' => 'iqrfRawHdp',
				'request' => '00.00.06.03.ff.ff',
				'id' => 1,
			],
		];
		Assert::equal($expected, $this->manager->list());
	}

	/**
	 * Test function to load configuration of Scheduler
	 */
	public function testLoad(): void {
		Assert::equal($this->array, $this->manager->load(0));
		Assert::equal([], $this->manager->load(10));
	}

	/**
	 * Test function to save configuration of Scheduler
	 */
	public function testSave(): void {
		\Tester\Environment::lock('config_scheduler', __DIR__ . '/../../temp/');
		$array = $this->array;
		$array['message']['nadr'] = '0';
		$expected = $this->fileManager->read($this->fileName);
		$this->fileManagerTemp->write($this->fileName, $expected);
		$expected['TasksJson'][0]['message']['nadr'] = '0';
		$this->manager->save($array, 0);
		Assert::equal($expected, $this->fileManagerTemp->read($this->fileName));
	}

	/**
	 * Set up the test environment
	 */
	protected function setUp(): void {
		$configPath = __DIR__ . '/../../data/configuration/';
		$configTempPath = __DIR__ . '/../../temp/configuration/';
		$schemaPath = __DIR__ . '/../../data/cfgSchemas/';
		$this->fileManager = new JsonFileManager($configPath . 'scheduler/');
		$this->fileManagerTemp = new JsonFileManager($configTempPath . 'scheduler/');
		$fileManager = new JsonFileManager($configPath);
		$schemaManager = new JsonSchemaManager($schemaPath);
		$genericConfigManager = new GenericManager($fileManager, $schemaManager);
		$configuration = ['cacheDir' => $configTempPath,];
		$mainConfigManager = \Mockery::mock(MainManager::class);
		$mainConfigManager->shouldReceive('load')->andReturn($configuration);
		$this->fileManagerTemp->write($this->fileName, $this->fileManager->read($this->fileName));
		$this->manager = new SchedulerManager($mainConfigManager, $genericConfigManager);
	}

	/**
	 * Cleanup the test environment
	 */
	protected function tearDown(): void {
		\Mockery::close();
	}

}

$test = new SchedulerManagerTest($container);
$test->run();
