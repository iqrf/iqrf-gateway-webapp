<?php

/**
 * TEST: App\ConfigModule\Model\SchedulerManager
 * @covers App\ConfigModule\Model\SchedulerManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\ConfigModule\Model;

use App\ConfigModule\Model\GenericManager;
use App\ConfigModule\Model\MainManager;
use App\ConfigModule\Model\SchedulerManager;
use App\CoreModule\Model\JsonFileManager;
use App\CoreModule\Model\JsonSchemaManager;
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
				'ctype' => 'dpa',
				'type' => 'raw',
				'msgid' => '1',
				'timeout' => 1000,
				'request' => '00.00.06.03.ff.ff',
				'request_ts' => '',
				'confirmation' => '',
				'confirmation_ts' => '',
				'response' => '',
				'response_ts' => '',
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
	 * Set up the test environment
	 */
	protected function setUp() {
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
	protected function tearDown() {
		\Mockery::close();
	}

	/**
	 * Test function to add configuration of Scheduler
	 */
	public function testAdd() {
		$expected = $this->fileManager->read($this->fileName);
		$this->fileManagerTemp->write($this->fileName, $expected);
		$this->manager->add('raw');
		$task = [
			'time' => '',
			'service' => '',
			'task' => [
				'messaging' => '',
				'message' => [
					'ctype' => 'dpa',
					'type' => 'raw',
					'msgid' => '',
					'timeout' => 0,
					'request' => '',
					'request_ts' => '',
					'confirmation' => '',
					'confirmation_ts' => '',
					'response' => '',
					'response_ts' => '',
				],],
		];
		array_push($expected['TasksJson'], $task);
		Assert::equal($expected, $this->fileManagerTemp->read($this->fileName));
	}

	/**
	 * Test function to delete configuration of Scheduler
	 */
	public function testDelete() {
		$expected = $this->fileManager->read($this->fileName);
		$this->fileManagerTemp->write($this->fileName, $expected);
		unset($expected['TasksJson'][5]);
		$this->manager->delete(5);
		Assert::equal($expected, $this->fileManagerTemp->read($this->fileName));
	}

	/**
	 * Test function to fix HWPID format
	 */
	public function testFixHwpid() {
		Assert::equal('01.00', $this->manager->fixHwpid('0001'));
	}

	/**
	 * Test function to get last ID
	 */
	public function testGetLastId() {
		$expected = count($this->fileManager->read($this->fileName)['TasksJson']) - 1;
		Assert::equal($expected, $this->manager->getLastId());
	}

	/**
	 * Test function to get avaiable messagings
	 */
	public function testGetMessagings() {
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
	public function testGetServices() {
		$expected = ['SchedulerMessaging'];
		Assert::same($expected, $this->manager->getServices());
	}

	/**
	 * Test function to get tasks
	 */
	public function testGetTasks() {
		$expected = [
			[
				'time' => '*/5 * 1 * * * *',
				'service' => 'SchedulerMessaging',
				'messaging' => 'WebsocketMessaging',
				'type' => 'dpa | raw',
				'request' => '00.00.06.03.ff.ff',
				'id' => 0,
			], [
				'time' => '*/5 * 1 * * * *',
				'service' => 'SchedulerMessaging',
				'messaging' => 'WebsocketMessaging',
				'type' => 'dpa | raw-hdp',
				'request' => '00.00.06.03.ff.ff',
				'id' => 1,
			],
		];
		Assert::equal($expected, $this->manager->getTasks());
	}

	/**
	 * Test function to load configuration of Scheduler
	 */
	public function testLoad() {
		Assert::equal($this->array, $this->manager->load(0));
		Assert::equal([], $this->manager->load(10));
	}

	/**
	 * Test function to save configuration of Scheduler
	 */
	public function testSave() {
		$array = $this->array;
		$array['message']['nadr'] = '0';
		$expected = $this->fileManager->read($this->fileName);
		$this->fileManagerTemp->write($this->fileName, $expected);
		$expected['TasksJson'][0]['message']['nadr'] = '0';
		$this->manager->save($array, 0);
		Assert::equal($expected, $this->fileManagerTemp->read($this->fileName));
	}

	/**
	 * Test function to parse configuration of Scheduler
	 */
	public function testSaveJson() {
		$update = $this->array;
		$update['task']['message']['msgid'] = '2';
		$json = $this->fileManager->read($this->fileName);
		$expected = $json;
		$expected['TasksJson'][0]['task']['message']['msgid'] = '2';
		Assert::equal($expected, $this->manager->saveJson($json, $update, 0));
	}

}

$test = new SchedulerManagerTest($container);
$test->run();
