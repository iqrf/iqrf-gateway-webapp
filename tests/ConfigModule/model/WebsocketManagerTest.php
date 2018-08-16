<?php

/**
 * TEST: App\ConfigModule\Model\WebsocketManager
 * @covers App\ConfigModule\Model\WebsocketManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\ConfigModule\Model;

use App\ConfigModule\Model\GenericManager;
use App\ConfigModule\Model\WebsocketManager;
use App\Model\JsonFileManager;
use App\Model\JsonSchemaManager;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for websocket interface configuration manager
 */
class WebsocketManagerTest extends TestCase {

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
	private $fileManagerTest;

	/**
	 * @var array Websocket messaging and service file names
	 */
	private $fileNames = [
		'messaging' => 'iqrf__WebsocketMessaging',
		'service' => 'shape__WebsocketCppService',
	];

	/**
	 * @var array Websocket instances
	 */
	private $instances = [
		'messaging' => 'WebsocketMessaging',
		'service' => 'WebsocketCppService',
	];

	/**
	 * @var WebsocketManager Websocket interface configuration manager
	 */
	private $manager;

	/**
	 * @var WebsocketManager Websocket interface configuration manager
	 */
	private $managerTest;

	/**
	 * @var string Directory with configuration files
	 */
	private $path = __DIR__ . '/../../configuration/';

	/**
	 * @var string Testing directory with configuration files
	 */
	private $pathTest = __DIR__ . '/../../configuration-test/';

	/**
	 * @var string Directory with JSON schemas
	 */
	private $schemaPath = __DIR__ . '/../../jsonschema/';

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
	 * Set up test environment
	 */
	public function setUp() {
		$this->fileManager = new JsonFileManager($this->path);
		$this->fileManagerTest = new JsonFileManager($this->pathTest);
		$schemaManager = new JsonSchemaManager($this->schemaPath);
		$genericManager = new GenericManager($this->fileManager, $schemaManager);
		$genericManagerTest = new GenericManager($this->fileManagerTest, $schemaManager);
		$this->manager = new WebsocketManager($genericManager, $this->fileManager, $schemaManager);
		$this->managerTest = new WebsocketManager($genericManagerTest, $this->fileManagerTest, $schemaManager);
	}

	/**
	 * Copy a configuration
	 */
	public function copyConfiguration() {
		foreach ($this->fileNames as $fileName) {
			$json = $this->fileManager->read($fileName);
			$this->fileManagerTest->write($fileName, $json);
		}
	}

	/**
	 * Test function to delete a websocket interface configuration
	 */
	public function testDelete() {
		$this->copyConfiguration();
		Assert::true($this->fileManagerTest->exists($this->fileNames['messaging']));
		Assert::true($this->fileManagerTest->exists($this->fileNames['service']));
		$this->managerTest->delete(0);
		Assert::false($this->fileManagerTest->exists($this->fileNames['messaging']));
		Assert::false($this->fileManagerTest->exists($this->fileNames['service']));
	}

	/**
	 * Test function to load a websocket interface configuration
	 */
	public function testLoad() {
		$expected = [
			'messagingInstance' => $this->instances['messaging'],
			'acceptAsyncMsg' => true,
			'serviceInstance' => $this->instances['service'],
			'port' => 1338,
		];
		Assert::same($expected, $this->manager->load(0));
	}

	/**
	 * Test function to save a websocket interface
	 */
	public function testSave() {
		$this->copyConfiguration();
		$this->managerTest->load(0);
		$this->managerTest->save($this->values);
		$expectedMessaging = $this->fileManager->read($this->fileNames['messaging']);
		$expectedMessaging['RequiredInterfaces'][0]['target']['instance'] = 'WebsocketCppService';
		unset($expectedMessaging['RequiredInterfaces'][0]['target']['WebsocketPort']);
		Assert::same($expectedMessaging, $this->fileManagerTest->read($this->fileNames['messaging']));
		Assert::same($this->fileManager->read($this->fileNames['service']), $this->fileManagerTest->read($this->fileNames['service']));
	}

	/**
	 * Test function to get a websocket interfaces
	 */
	public function testGetInstances() {
		$expected = [
			[
				'messagingInstance' => 'WebsocketMessaging',
				'acceptAsyncMsg' => true,
				'serviceInstance' => 'WebsocketCppService',
				'port' => 1338,
			], [
				'messagingInstance' => 'WebsocketMessagingMobileApp',
				'acceptAsyncMsg' => true,
				'serviceInstance' => 'WebsocketCppServiceMobileApp',
				'port' => 1339,
			], [
				'messagingInstance' => 'WebsocketMessagingWebApp',
				'acceptAsyncMsg' => true,
				'serviceInstance' => 'WebsocketCppServiceWebApp',
				'port' => 1340,
			],
		];
		Assert::same($expected, $this->manager->getInstances());
	}

	/**
	 * Test function to create a websocket messaging
	 */
	public function testCreateMessaging() {
		$expected = [
			'component' => 'iqrf::WebsocketMessaging',
			'instance' => $this->instances['messaging'],
			'acceptAsyncMsg' => $this->values['acceptAsyncMsg'],
			'RequiredInterfaces' => [
				(object) [
					'name' => 'shape::IWebsocketService',
					'target' => (object) [
						'instance' => $this->instances['service'],
					],
				],
			],
		];
		Assert::equal($expected, $this->manager->createMessaging($this->values, $this->instances));
	}

	/**
	 * Test function to create a websocket service
	 */
	public function testCreateService() {
		$expected = [
			'component' => 'shape::WebsocketCppService',
			'instance' => $this->instances['service'],
			'WebsocketPort' => $this->values['port'],
		];
		Assert::same($expected, $this->manager->createService($this->values, $this->instances));
	}

	/**
	 * Test function to get websocket service file name by instance name
	 */
	public function testGetServiceFile() {
		Assert::same($this->fileNames['service'], $this->manager->getServiceFile($this->instances['service']));
	}

}

$test = new WebsocketManagerTest($container);
$test->run();
