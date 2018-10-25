<?php

/**
 * TEST: App\CloudModule\Models\InteliGlueManager
 * @covers App\CloudModule\Models\InteliGlueManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\ServiceModule\Models;

use App\CloudModule\Models\InteliGlueManager;
use App\ConfigModule\Models\GenericManager;
use App\CoreModule\Models\JsonFileManager;
use App\CoreModule\Models\JsonSchemaManager;
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
 * Tests for Inteliment InteliGlue manager
 */
class InteliGlueManagerTest extends TestCase {

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
	 * @var \Mockery\Mock Mocked Inteliments InteliGlue manager
	 */
	private $manager;

	/**
	 * @var array Values from Inteliments InteliGlue form
	 */
	private $formValues = [
		'assignedPort' => 1234,
		'clientId' => 'client1234',
		'password' => 'pass1234',
		'rootTopic' => 'root',
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
	 * Test function to create MQTT interface
	 */
	public function testCreateMqttInterface(): void {
		$mqtt = [
			'component' => 'iqrf::MqttMessaging',
			'instance' => 'MqttMessagingInteliGlue',
			'BrokerAddr' => 'ssl://mqtt.inteliglue.com:1234',
			'ClientId' => 'client1234',
			'Persistence' => 1,
			'Qos' => 0,
			'TopicRequest' => 'root/Iqrf/DpaRequest',
			'TopicResponse' => 'root/Iqrf/DpaResponse',
			'User' => 'client1234',
			'Password' => 'pass1234',
			'EnabledSSL' => true,
			'KeepAliveInterval' => 20,
			'ConnectTimeout' => 5,
			'MinReconnect' => 1,
			'MaxReconnect' => 64,
			'TrustStore' => $this->certPath . 'inteliments-ca.crt',
			'KeyStore' => '',
			'PrivateKey' => '',
			'PrivateKeyPassword' => '',
			'EnabledCipherSuites' => '',
			'EnableServerCertAuth' => false,
			'acceptAsyncMsg' => false,
		];
		$this->manager->createMqttInterface($this->formValues);
		Assert::same($mqtt, $this->fileManager->read('iqrf__MqttMessaging_InteliGlue'));
	}

	/**
	 * Test function to download root CA certificate
	 */
	public function testDownloadCaCertificate(): void {
		$expected = 'inteliments-ca.crt';
		$responseMock = new MockHandler([
			new Response(200, [], $expected),
		]);
		$handler = HandlerStack::create($responseMock);
		$client = new Client(['handler' => $handler]);
		$manager = new InteliGlueManager($this->certPath, $this->configManager, $client);
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
		$this->manager = \Mockery::mock(InteliGlueManager::class, [$this->certPath, $this->configManager, $client])->makePartial();
		$this->manager->shouldReceive('downloadCaCertificate')->andReturn(null);
	}

	/**
	 * Cleanup the test environment
	 */
	protected function tearDown(): void {
		\Mockery::close();
	}

}

$test = new InteliGlueManagerTest($container);
$test->run();
