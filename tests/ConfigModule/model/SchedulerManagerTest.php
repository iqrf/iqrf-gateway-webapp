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
use App\Model\JsonFileManager;
use App\Model\JsonSchemaManager;
use Nette\DI\Container;
use Nette\Utils\ArrayHash;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

class SchedulerManagerTest extends TestCase {

	/**
	 * @var Container Nette Tester Container
	 */
	private $container;

	/**
	 * @var GenericManager Generic configuration manager
	 */
	private $genericConfigManager;

	/**
	 * @var \Mockery\MockInterface Mocked main configuration manager
	 */
	private $mainConfigManager;

	/**
	 * @var JsonFileManager JSON file manager
	 */
	private $fileManager;

	/**
	 * @var JsonFileManager JSON file manager
	 */
	private $fileManagerTemp;

	/**
	 * @var array
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
	 * @var string Directory with configuration files
	 */
	private $path = __DIR__ . '/../../configuration/Scheduler/';

	/**
	 * @var string Testing directory with configuration files
	 */
	private $pathTest = __DIR__ . '/../../configuration-test/Scheduler/';

	/**
	 * @var string Directory with JSON schemas
	 */
	private $schemaPath = __DIR__ . '/../../jsonschema/';

	/**
	 * Constructor
	 * @param Container $container Nette Tester Container
	 */
	public function __construct(Container $container) {
		$this->container = $container;
	}

	/**
	 * Set up test environment
	 */
	public function setUp() {
		$this->fileManager = new JsonFileManager($this->path);
		$this->fileManagerTemp = new JsonFileManager($this->pathTest);
		$fileManager = new JsonFileManager(realpath($this->path . '/../'));
		$schemaManager = new JsonSchemaManager($this->schemaPath);
		$this->genericConfigManager = new GenericManager($fileManager, $schemaManager);
		$this->mainConfigManager = \Mockery::mock(MainManager::class);
		$configuration = [
			'cacheDir' => realpath($this->pathTest . '/../'),
		];
		$this->mainConfigManager->shouldReceive('load')->andReturn($configuration);
		$this->fileManagerTemp->write($this->fileName, $this->fileManager->read($this->fileName));
	}

	/**
	 * @test
	 * Test function to add configuration of Scheduler
	 */
	public function testAdd() {
		$manager = new SchedulerManager($this->mainConfigManager, $this->genericConfigManager);
		$expected = $this->fileManager->read($this->fileName);
		$this->fileManagerTemp->write($this->fileName, $expected);
		$manager->add('raw');
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
	 * @test
	 * Test function to delete configuration of Scheduler
	 */
	public function testDelete() {
		$manager = new SchedulerManager($this->mainConfigManager, $this->genericConfigManager);
		$expected = $this->fileManager->read($this->fileName);
		$this->fileManagerTemp->write($this->fileName, $expected);
		unset($expected['TasksJson'][5]);
		$manager->delete(5);
		Assert::equal($expected, $this->fileManagerTemp->read($this->fileName));
	}

	/**
	 * @test
	 * Test function to fix HWPID format
	 */
	public function testFixHwpid() {
		$manager = new SchedulerManager($this->mainConfigManager, $this->genericConfigManager);
		Assert::equal('01.00', $manager->fixHwpid('0001'));
	}

	/**
	 * @test
	 * Test function to get last ID
	 */
	public function testGetLastId() {
		$manager = new SchedulerManager($this->mainConfigManager, $this->genericConfigManager);
		$expected = count($this->fileManager->read($this->fileName)['TasksJson']) - 1;
		Assert::equal($expected, $manager->getLastId());
	}

	/**
	 * @test
	 * Test function to get avaiable messagings
	 */
	public function testGetMessagings() {
		$manager = new SchedulerManager($this->mainConfigManager, $this->genericConfigManager);
		$expected = [
			'config.mq.title' => ['MqMessaging',],
			'config.mqtt.title' => ['MqttMessaging1', 'MqttMessaging2',],
			'config.udp.title' => ['UdpMessaging',],
			'config.websocket.title' => ['WebsocketMessaging',],
		];
		Assert::same($expected, $manager->getMessagings());
	}

	/**
	 * @test
	 * Test function to get scheduler's services
	 */
	public function testGetServices() {
		$manager = new SchedulerManager($this->mainConfigManager, $this->genericConfigManager);
		$expected = ['SchedulerMessaging'];
		Assert::same($expected, $manager->getServices());
	}

	/**
	 * @test
	 * Test function to get tasks
	 */
	public function testGetTasks() {
		$manager = new SchedulerManager($this->mainConfigManager, $this->genericConfigManager);
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
		Assert::equal($expected, $manager->getTasks());
	}

	/**
	 * @test
	 * Test function to load configuration of Scheduler
	 */
	public function testLoad() {
		$manager = new SchedulerManager($this->mainConfigManager, $this->genericConfigManager);
		Assert::equal($this->array, $manager->load(0));
		Assert::equal([], $manager->load(10));
	}

	/**
	 * @test
	 * Test function to save configuration of Scheduler
	 */
	public function testSave() {
		$manager = new SchedulerManager($this->mainConfigManager, $this->genericConfigManager);
		$array = $this->array;
		$array['message']['nadr'] = '0';
		$expected = $this->fileManager->read($this->fileName);
		$this->fileManagerTemp->write($this->fileName, $expected);
		$expected['TasksJson'][0]['message']['nadr'] = '0';
		$manager->save(ArrayHash::from($array), 0);
		Assert::equal($expected, $this->fileManagerTemp->read($this->fileName));
	}

	/**
	 * @test
	 * Test function to parse configuration of Scheduler
	 */
	public function testSaveJson() {
		$manager = new SchedulerManager($this->mainConfigManager, $this->genericConfigManager);
		$updateArray = $this->array;
		$updateArray['task']['message']['msgid'] = '2';
		$json = $this->fileManager->read($this->fileName);
		$expected = $json;
		$expected['TasksJson'][0]['task']['message']['msgid'] = '2';
		$update = ArrayHash::from($updateArray);
		Assert::equal($expected, $manager->saveJson($json, $update, 0));
	}

}

$test = new SchedulerManagerTest($container);
$test->run();
