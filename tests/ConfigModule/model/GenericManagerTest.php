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

class GenericManagerTest extends TestCase {

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
	 * @var string File name (without .json)
	 */
	private $fileName = 'iqrf__MqttMessaging1';

	/**
	 * @var string Directory with configuration files
	 */
	private $path = __DIR__ . '/../../configuration/';

	/**
	 * @var string Testing directory with configuration files
	 */
	private $pathTest = __DIR__ . '/../../configuration-test/';

	/**
	 * @var JsonSchemaManager JSON schema manager
	 */
	private $schemaManager;

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
		$this->schemaManager = new JsonSchemaManager($this->schemaPath);
	}

	/**
	 * Test function to get component's instances
	 */
	public function testGetInstances() {
		$manager = new GenericManager($this->fileManager, $this->schemaManager);
		$manager->setComponent('iqrf::MqttMessaging');
		$expected = ['iqrf__MqttMessaging1', 'iqrf__MqttMessaging2',];
		Assert::equal($expected, $manager->getInstanceFiles());
	}

	/**
	 * Test function to get avaiable messagings
	 */
	public function testGetMessagings() {
		$manager = new GenericManager($this->fileManager, $this->schemaManager);
		$expected = [
			'config.mq.title' => ['MqMessaging',],
			'config.mqtt.title' => ['MqttMessaging1', 'MqttMessaging2',],
			'config.udp.title' => ['UdpMessaging',],
			'config.websocket.title' => ['WebsocketMessaging',],
		];
		Assert::same($expected, $manager->getMessagings());
	}

	/**
	 * Test function to load main configuration of daemon
	 */
	public function testLoad() {
		$manager = new GenericManager($this->fileManager, $this->schemaManager);
		$manager->setFileName($this->fileName);
		$expected = $this->fileManager->read($this->fileName);
		Assert::equal($expected, $manager->load());
	}

	/**
	 * Test function to save main configuration of daemon
	 */
	public function testSave() {
		$manager = new GenericManager($this->fileManagerTemp, $this->schemaManager);
		$manager->setComponent('iqrf::MqttMessaging');
		$manager->setFileName($this->fileName);
		$array = [
			'instance' => 'MqttMessaging1',
			'BrokerAddr' => 'tcp://127.0.0.1:1883',
			'ClientId' => 'IqrfDpaMessaging1',
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
		$manager->save(ArrayHash::from($array));
		Assert::equal($expected, $this->fileManagerTemp->read($this->fileName));
	}

}

$test = new GenericManagerTest($container);
$test->run();
