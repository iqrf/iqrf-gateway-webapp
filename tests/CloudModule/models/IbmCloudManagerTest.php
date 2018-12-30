<?php

/**
 * TEST: App\CloudModule\Models\BluemixManager
 * @covers App\CloudModule\Models\BluemixManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\ServiceModule\Models;

use App\CloudModule\Models\IbmCloudManager;
use App\ConfigModule\Models\GenericManager;
use App\CoreModule\Models\JsonFileManager;
use App\CoreModule\Models\JsonSchemaManager;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Mockery\Mock;
use Nette\Utils\FileSystem;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for IBM Cloud manager
 */
class IbmCloudManagerTest extends TestCase {

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
	 * @var Mock|IbmCloudManager Mocked IBM Bluemix manager
	 */
	private $manager;

	/**
	 * @var string[] Values from IBM Cloud form
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
	 */
	public function __construct() {
		$this->certPath = realpath(__DIR__ . '/../../temp/certificates/') . '/';
	}

	/**
	 * Test function to create MQTT interface
	 */
	public function testCreateMqttInterface(): void {
		$mqtt = [
			'component' => 'iqrf::MqttMessaging',
			'instance' => 'MqttMessagingIbmCloud',
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
			'TrustStore' => $this->certPath . 'ibm-cloud-ca.crt',
			'KeyStore' => '',
			'PrivateKey' => '',
			'PrivateKeyPassword' => '',
			'EnabledCipherSuites' => '',
			'EnableServerCertAuth' => false,
			'acceptAsyncMsg' => false,
		];
		$this->manager->createMqttInterface($this->formValues);
		Assert::same($mqtt, $this->fileManager->read('iqrf__MqttMessaging_IbmCloud'));
	}

	/**
	 * Test function to download root CA certificate
	 */
	public function testDownloadCaCertificate(): void {
		$expected = 'ibm-cloud-ca.crt';
		$responseMock = new MockHandler([
			new Response(200, [], $expected),
		]);
		$handler = HandlerStack::create($responseMock);
		$client = new Client(['handler' => $handler]);
		$manager = new IbmCloudManager($this->certPath, $this->configManager, $client);
		$manager->downloadCaCertificate();
		Assert::same($expected, FileSystem::read($this->certPath . $expected));
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
		$this->manager = Mockery::mock(IbmCloudManager::class, [$this->certPath, $this->configManager, $client])->makePartial();
		$this->manager->shouldReceive('downloadCaCertificate')->andReturn(null);
	}

	/**
	 * Cleanup the test environment
	 */
	protected function tearDown(): void {
		Mockery::close();
	}

}

$test = new IbmCloudManagerTest();
$test->run();
