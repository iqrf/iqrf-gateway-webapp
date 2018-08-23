<?php

/**
 * TEST: App\CloudModule\Model\BluemixManager
 * @covers App\CloudModule\Model\BluemixManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\ServiceModule\Model;

use App\CloudModule\Model\BluemixManager;
use App\ConfigModule\Model\GenericManager;
use App\CoreModule\Model\JsonFileManager;
use App\CoreModule\Model\JsonSchemaManager;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for IBM Bluemix manager
 */
class BluemixManagerTest extends TestCase {

	/**
	 * @var Container Nette Tester Container
	 */
	private $container;

	/**
	 * @var JsonFileManager JSON file manager
	 */
	private $fileManager;

	/**
	 * @var \Mockery\Mock Mocked IBM Bluemix manager
	 */
	private $manager;

	/**
	 * @var array Values from IBM Bluemix form
	 */
	private $formValues = [
		'deviceId' => 'gw00',
		'deviceType' => 'gateway',
		'eventId' => 'event1234',
		'organizationId' => 'org1234',
		'token' => 'token1234',
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
		$configPath = __DIR__ . '/../../temp/configuration/';
		$schemaPath = __DIR__ . '/../../data/cfgSchemas/';
		$this->fileManager = new JsonFileManager($configPath);
		$schemaManager = new JsonSchemaManager($schemaPath);
		$configManager = new GenericManager($this->fileManager, $schemaManager);
		$this->manager = \Mockery::mock(BluemixManager::class, [$configManager])->makePartial();
		$this->manager->shouldReceive('downloadCaCertificate')->andReturn(null);
	}

	/**
	 * Test function to create MQTT interface
	 */
	public function testCreateMqttInterface() {
		$mqtt = [
			'component' => 'iqrf::MqttMessaging',
			'instance' => 'MqttMessagingBluemix',
			'BrokerAddr' => 'ssl://org1234.messaging.internetofthings.ibmcloud.com:8883',
			'ClientId' => 'd:org1234:gateway:gw00',
			'Persistence' => 1,
			'Qos' => 0,
			'TopicRequest' => 'iot-2/cmd/event1234/fmt/json',
			'TopicResponse' => 'iot-2/evt/event1234/fmt/json',
			'User' => 'use-token-auth',
			'Password' => 'token1234',
			'EnabledSSL' => true,
			'KeepAliveInterval' => 20,
			'ConnectTimeout' => 5,
			'MinReconnect' => 1,
			'MaxReconnect' => 64,
			'TrustStore' => '/etc/iqrf-daemon/certs/bluemix-ca.crt',
			'KeyStore' => '',
			'PrivateKey' => '',
			'PrivateKeyPassword' => '',
			'EnabledCipherSuites' => '',
			'EnableServerCertAuth' => false,
			'acceptAsyncMsg' => false,
		];
		$this->manager->createMqttInterface($this->formValues);
		Assert::same($mqtt, $this->fileManager->read('MqttMessagingBluemix'));
	}

}

$test = new BluemixManagerTest($container);
$test->run();
