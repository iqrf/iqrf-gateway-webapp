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
use App\CoreModule\Model\JsonFileManager;
use App\CoreModule\Model\JsonSchemaManager;
use Nette\DI\Container;
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
	private $fileManagerTemp;

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
	private $managerTemp;

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
		$this->fileManager = new JsonFileManager($configPath);
		$this->fileManagerTemp = new JsonFileManager($configTempPath);
		$schemaManager = new JsonSchemaManager($schemaPath);
		$this->manager = new GenericManager($this->fileManager, $schemaManager);
		$this->managerTemp = new GenericManager($this->fileManagerTemp, $schemaManager);
	}

	/**
	 * Test function to delete the instance of component
	 */
	public function testDelete() {
		$this->fileManagerTemp->write($this->fileName, $this->fileManager->read($this->fileName));
		Assert::true($this->fileManagerTemp->exists($this->fileName));
		$this->managerTemp->setFileName($this->fileName);
		$this->managerTemp->delete();
		Assert::false($this->fileManagerTemp->exists($this->fileName));
	}

	/**
	 * Test function to fix a required instance in the configuration
	 */
	public function testFixRequiredInterfaces() {
		$expected = $this->fileManager->read('iqrf__WebsocketMessaging');
		$configuration = $expected;
		$expected['RequiredInterfaces'][0]['target']['instance'] = 'WebsocketCppService';
		unset($expected['RequiredInterfaces'][0]['target']['WebsocketPort']);
		$this->manager->fixRequiredInterfaces($configuration);
		Assert::same($expected, $configuration);
	}

	/**
	 * Test function to generate a file name
	 */
	public function testGenerateFileName() {
		$this->manager->setComponent($this->component);
		$array = $this->fileManager->read($this->fileName);
		$this->manager->generateFileName($array);
		Assert::equal($this->fileName, $this->manager->getFileName());
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
		$this->managerTemp->setComponent($this->component);
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
		$this->fileManagerTemp->write($this->fileName, $expected);
		$expected['acceptAsyncMsg'] = true;
		$this->managerTemp->save($array);
		Assert::equal($expected, $this->fileManagerTemp->read($this->fileName));
	}

}

$test = new GenericManagerTest($container);
$test->run();
