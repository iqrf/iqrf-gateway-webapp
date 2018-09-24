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
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Nette\DI\Container;
use Nette\Utils\FileSystem;
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
	 * @var string Path to a directory with certificates and private keys
	 */
	private $certPath;

	/**
	 * @var GenericManager Generic configuration manager
	 */
	private $configManager;

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
		$this->certPath = realpath(__DIR__ . '/../../temp/certificates/') . '/';
	}

	/**
	 * Set up the test environment
	 */
	protected function setUp(): void {
		$configPath = __DIR__ . '/../../temp/configuration/';
		$schemaPath = __DIR__ . '/../../data/cfgSchemas/';
		$this->fileManager = new JsonFileManager($configPath);
		$schemaManager = new JsonSchemaManager($schemaPath);
		$this->configManager = new GenericManager($this->fileManager, $schemaManager);
		$client = new Client();
		$this->manager = \Mockery::mock(BluemixManager::class, [$this->certPath, $this->configManager, $client])->makePartial();
		$this->manager->shouldReceive('downloadCaCertificate')->andReturn(null);
	}

	/**
	 * Cleanup the test environment
	 */
	protected function tearDown(): void {
		\Mockery::close();
	}

	/**
	 * Test function to create MQTT interface
	 */
	public function testCreateMqttInterface(): void {
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
			'TrustStore' => $this->certPath . 'bluemix-ca.crt',
			'KeyStore' => '',
			'PrivateKey' => '',
			'PrivateKeyPassword' => '',
			'EnabledCipherSuites' => '',
			'EnableServerCertAuth' => false,
			'acceptAsyncMsg' => false,
		];
		$this->manager->createMqttInterface($this->formValues);
		Assert::same($mqtt, $this->fileManager->read('iqrf__MqttMessaging_Bluemix'));
	}

	/**
	 * Test function to download root CA certificate
	 */
	public function testDownloadCaCertificate(): void {
		$expected = 'bluemix-ca.crt';
		$responseMock = new MockHandler([
			new Response(200, [], $expected),
		]);
		$handler = HandlerStack::create($responseMock);
		$client = new Client(['handler' => $handler]);
		$manager = new BluemixManager($this->certPath, $this->configManager, $client);
		$manager->downloadCaCertificate();
		Assert::same($expected, FileSystem::read($this->certPath . $expected));
	}

}

$test = new BluemixManagerTest($container);
$test->run();
