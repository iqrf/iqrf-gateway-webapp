<?php

/**
 * TEST: App\CloudModule\Model\BluemixManager
 * @covers App\CloudModule\Model\BluemixManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types=1);

namespace Test\ServiceModule\Model;

use App\CloudModule\Model\BluemixManager;
use App\ConfigModule\Model\GenericManager;
use App\Model\JsonFileManager;
use Nette\DI\Container;
use Nette\Utils\ArrayHash;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

class BluemixManagerTest extends TestCase {

	/**
	 * @var GenericManager Generic configuration manager
	 */
	private $configManager;

	/**
	 * @var Container Nette Tester Container
	 */
	private $container;

	/**
	 * @var JsonFileManager JSON file manager
	 */
	private $fileManager;

	/**
	 * @var BluemixManager Amazon AWS IoT manager
	 */
	private $manager;

	/**
	 * @var array Values from Inteliments InteliGlue form
	 */
	private $formValues = [
		'deviceId' => 'gw00',
		'deviceType' => 'gateway',
		'eventId' => 'event1234',
		'organizationId' => 'org1234',
		'token' => 'token1234',
	];

	/**
	 * @var string Testing directory with configuration files
	 */
	private $pathTest = __DIR__ . '/../../configuration-test/';

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
		$this->fileManager = new JsonFileManager($this->pathTest);
		$this->configManager = new GenericManager($this->fileManager);
		$this->manager = \Mockery::mock(BluemixManager::class, [$this->configManager])->makePartial();
		$this->manager->shouldReceive('downloadCaCertificate')->andReturn(null);
	}

	/**
	 * @test
	 * Test function to create MQTT interface
	 */
	public function testCreateMqttInterface() {
		$values = ArrayHash::from($this->formValues);
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
		$this->manager->createMqttInterface($values);
		Assert::same($mqtt, $this->fileManager->read('MqttMessagingBluemix'));
	}

}

$test = new BluemixManagerTest($container);
$test->run();
