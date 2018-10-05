<?php

/**
 * TEST: App\ConfigModule\Model\WebSocketManager
 * @covers App\ConfigModule\Model\WebSocketManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\ConfigModule\Model;

use App\ConfigModule\Model\GenericManager;
use App\ConfigModule\Model\WebSocketManager;
use App\CoreModule\Model\JsonFileManager;
use App\CoreModule\Model\JsonSchemaManager;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for WebSocket interface configuration manager
 */
class WebSocketManagerTest extends TestCase {

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
	 * @var array WebSocket messaging and service file names
	 */
	private $fileNames = [
		'messaging' => 'iqrf__WebsocketMessaging',
		'service' => 'shape__WebsocketCppService',
	];

	/**
	 * @var array WebSocket instances
	 */
	private $instances = [
		'messaging' => 'WebsocketMessaging',
		'service' => 'WebsocketCppService',
	];

	/**
	 * @var WebSocketManager WebSocket interface configuration manager
	 */
	private $manager;

	/**
	 * @var WebSocketManager WebSocket interface configuration manager
	 */
	private $managerTemp;

	/**
	 * @var array Values from configuration form
	 */
	private $values = [
		'acceptAsyncMsg' => true,
		'port' => 1338,
	];

	/**
	 * Constructor
	 * @param Container $container Nette Tester Container
	 */
	public function __construct(Container $container) {
		$this->container = $container;
	}

	/**
	 * Test function to delete a WebSocket interface configuration
	 */
	public function testDelete(): void {
		\Tester\Environment::lock('config_websocket', __DIR__ . '/../../temp/');
		$this->copyConfiguration();
		Assert::true($this->fileManagerTemp->exists($this->fileNames['messaging']));
		Assert::true($this->fileManagerTemp->exists($this->fileNames['service']));
		$this->managerTemp->delete(0);
		Assert::false($this->fileManagerTemp->exists($this->fileNames['messaging']));
		Assert::false($this->fileManagerTemp->exists($this->fileNames['service']));
	}

	/**
	 * Copy a configuration
	 */
	private function copyConfiguration(): void {
		foreach ($this->fileNames as $fileName) {
			$json = $this->fileManager->read($fileName);
			$this->fileManagerTemp->write($fileName, $json);
		}
	}

	/**
	 * Test function to load a WebSocket interface configuration
	 */
	public function testLoad(): void {
		$expected = [
			'messagingInstance' => $this->instances['messaging'],
			'acceptAsyncMsg' => true,
			'serviceInstance' => $this->instances['service'],
			'port' => 1338,
		];
		Assert::same($expected, $this->manager->load(0));
	}

	/**
	 * Test function to save a WebSocket interface
	 */
	public function testSave(): void {
		\Tester\Environment::lock('config_websocket', __DIR__ . '/../../temp/');
		$this->copyConfiguration();
		$this->managerTemp->load(0);
		$this->managerTemp->save($this->values);
		$expectedMessaging = $this->fileManager->read($this->fileNames['messaging']);
		$expectedMessaging['RequiredInterfaces'][0]['target']['instance'] = 'WebsocketCppService';
		unset($expectedMessaging['RequiredInterfaces'][0]['target']['WebsocketPort']);
		Assert::same($expectedMessaging, $this->fileManagerTemp->read($this->fileNames['messaging']));
		Assert::same($this->fileManager->read($this->fileNames['service']), $this->fileManagerTemp->read($this->fileNames['service']));
	}

	/**
	 * Test function to get a WebSocket interfaces
	 */
	public function testList(): void {
		$expected = [
			[
				'id' => 0,
				'messagingInstance' => 'WebsocketMessaging',
				'acceptAsyncMsg' => true,
				'serviceInstance' => 'WebsocketCppService',
				'port' => 1338,
			], [
				'id' => 1,
				'messagingInstance' => 'WebsocketMessagingMobileApp',
				'acceptAsyncMsg' => true,
				'serviceInstance' => 'WebsocketCppServiceMobileApp',
				'port' => 1339,
			], [
				'id' => 2,
				'messagingInstance' => 'WebsocketMessagingWebApp',
				'acceptAsyncMsg' => true,
				'serviceInstance' => 'WebsocketCppServiceWebApp',
				'port' => 1340,
			],
		];
		Assert::same($expected, $this->manager->list());
	}

	/**
	 * Test function to create a WebSocket messaging
	 */
	public function testCreateMessaging(): void {
		$expected = [
			'component' => 'iqrf::WebsocketMessaging',
			'instance' => $this->instances['messaging'],
			'acceptAsyncMsg' => $this->values['acceptAsyncMsg'],
			'RequiredInterfaces' => [
				(object)[
					'name' => 'shape::IWebsocketService',
					'target' => (object)[
						'instance' => $this->instances['service'],
					],
				],
			],
		];
		Assert::equal($expected, $this->manager->createMessaging($this->values, $this->instances));
	}

	/**
	 * Test function to create a WebSocket service
	 */
	public function testCreateService(): void {
		$expected = [
			'component' => 'shape::WebsocketCppService',
			'instance' => $this->instances['service'],
			'WebsocketPort' => $this->values['port'],
		];
		Assert::same($expected, $this->manager->createService($this->values, $this->instances));
	}

	/**
	 * Test function to get WebSocket service file name by instance name
	 */
	public function testGetServiceFile(): void {
		Assert::same($this->fileNames['service'], $this->manager->getServiceFile($this->instances['service']));
	}

	/**
	 * Set up the test environment
	 */
	protected function setUp(): void {
		$configPath = __DIR__ . '/../../data/configuration/';
		$configTempPath = __DIR__ . '/../../temp/configuration/';
		$schemaPath = __DIR__ . '/../../data/cfgSchemas/';
		$this->fileManager = new JsonFileManager($configPath);
		$this->fileManagerTemp = new JsonFileManager($configTempPath);
		$schemaManager = new JsonSchemaManager($schemaPath);
		$genericManager = new GenericManager($this->fileManager, $schemaManager);
		$genericManagerTest = new GenericManager($this->fileManagerTemp, $schemaManager);
		$this->manager = new WebSocketManager($genericManager, $this->fileManager, $schemaManager);
		$this->managerTemp = new WebSocketManager($genericManagerTest, $this->fileManagerTemp, $schemaManager);
	}

}

$test = new WebSocketManagerTest($container);
$test->run();
