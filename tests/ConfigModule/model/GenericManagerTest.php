<?php

/**
 * TEST: App\ConfigModule\Model\GenericManager
 * @covers App\ConfigModule\Model\GenericManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\ConfigModule\Model;

use App\ConfigModule\Model\GenericManager;
use App\Model\JsonFileManager;
use App\Model\JsonSchemaManager;
use Nette\DI\Container;
use Nette\Utils\ArrayHash;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for generic configuration manager
 */
class GenericManagerTest extends TestCase {

	/**
	 * @var Container Nette Tester Container
	 */
	private $container;

	/**
	 * @var string COmponent name
	 */
	private $component = 'iqrf::MqttMessaging';

	/**
	 * @var JsonFileManager JSON file manager
	 */
	private $fileManager;

	/**
	 * @var JsonFileManager JSON file manager
	 */
	private $fileManagerTest;

	/**
	 * @var string File name (without .json)
	 */
	private $fileName = 'iqrf__MqttMessaging';

	/**
	 * @var GenericManager Generic configuration manager
	 */
	private $manager;

	/**
	 * @var GenericManager Generic configuration manager
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
		$this->manager = new GenericManager($this->fileManager, $schemaManager);
		$this->managerTest = new GenericManager($this->fileManagerTest, $schemaManager);
	}

	/**
	 * Test function to delete the instance of component
	 */
	public function testDelete() {
		$this->fileManagerTest->write($this->fileName, $this->fileManager->read($this->fileName));
		Assert::true($this->fileManagerTest->exists($this->fileName));
		$this->managerTest->setFileName($this->fileName);
		$this->managerTest->delete();
		Assert::false($this->fileManagerTest->exists($this->fileName));
	}

	/**
	 * Test function to fix a required instance in the configuration
	 */
	public function testFixRequiredInterfaces() {
		$expected = $this->fileManager->read('iqrf__WebsocketMessaging');
		$configuration = $expected;
		$expected['RequiredInterfaces'][0]['target']['instance'] = 'WebsocketService';
		Assert::same($expected, $this->manager->fixRequiredInterfaces($configuration));
	}

	/**
	 * Test function to get instance by it's property
	 */
	public function testGetInstanceByProperty() {
		Assert::same($this->fileName, $this->manager->getInstanceByProperty('instance', 'MqttMessaging'));
		Assert::same($this->fileName, $this->manager->getInstanceByProperty('BrokerAddr', 'tcp://127.0.0.1:1883'));
	}

	/**
	 * Test function to get component's instances
	 */
	public function testGetInstances() {
		$this->manager->setComponent($this->component);
		$expected = ['iqrf__MqttMessaging',];
		Assert::equal($expected, $this->manager->getInstanceFiles());
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
	 * Test function to load main configuration of daemon
	 */
	public function testLoad() {
		$this->manager->setFileName($this->fileName);
		$expected = $this->fileManager->read($this->fileName);
		Assert::equal($expected, $this->manager->load());
	}

	/**
	 * Test function to save main configuration of daemon
	 */
	public function testSave() {
		$this->managerTest->setComponent($this->component);
		$this->managerTest->setFileName($this->fileName);
		$array = [
			'instance' => 'MqttMessaging',
			'BrokerAddr' => 'tcp://127.0.0.1:1883',
			'ClientId' => 'IqrfDpaMessaging',
			'Persistence' => 1,
			'Qos' => 1,
			'TopicRequest' => 'Iqrf/DpaRequest',
			'TopicResponse' => 'Iqrf/DpaResponse',
			'User' => '',
			'Password' => '',
			'EnabledSSL' => false,
			'KeepAliveInterval' => 20,
			'ConnectTimeout' => 5,
			'MinReconnect' => 1,
			'MaxReconnect' => 64,
			'TrustStore' => 'server-ca.crt',
			'KeyStore' => 'client.pem',
			'PrivateKey' => 'client-privatekey.pem',
			'PrivateKeyPassword' => '',
			'EnabledCipherSuites' => '',
			'EnableServerCertAuth' => true,
			'acceptAsyncMsg' => true,
		];
		$expected = $this->fileManager->read($this->fileName);
		$this->fileManagerTest->write($this->fileName, $expected);
		$expected['acceptAsyncMsg'] = true;
		$this->managerTest->save(ArrayHash::from($array));
		Assert::equal($expected, $this->fileManagerTest->read($this->fileName));
	}

}

$test = new GenericManagerTest($container);
$test->run();
