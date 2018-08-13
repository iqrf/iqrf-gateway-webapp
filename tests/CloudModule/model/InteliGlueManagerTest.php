<?php

/**
 * TEST: App\CloudModule\Model\InteliGlueManager
 * @covers App\CloudModule\Model\InteliGlueManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\ServiceModule\Model;

use App\CloudModule\Model\InteliGlueManager;
use App\ConfigModule\Model\GenericManager;
use App\Model\JsonFileManager;
use App\Model\JsonSchemaManager;
use Nette\DI\Container;
use Nette\Utils\ArrayHash;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

class InteliGlueManagerTest extends TestCase {

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
		$this->fileManager = new JsonFileManager($this->pathTest);
		$schemaManager = new JsonSchemaManager($this->schemaPath);
		$this->configManager = new GenericManager($this->fileManager, $schemaManager);
		$this->manager = \Mockery::mock(InteliGlueManager::class, [$this->configManager])->makePartial();
		$this->manager->shouldReceive('downloadCaCertificate')->andReturn(null);
	}

	/**
	 * Test function to create MQTT interface
	 */
	public function testCreateMqttInterface() {
		$values = ArrayHash::from($this->formValues);
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
			'TrustStore' => '/etc/iqrf-daemon/certs/inteliments-ca.crt',
			'KeyStore' => '',
			'PrivateKey' => '',
			'PrivateKeyPassword' => '',
			'EnabledCipherSuites' => '',
			'EnableServerCertAuth' => false,
			'acceptAsyncMsg' => false,
		];
		$this->manager->createMqttInterface($values);
		Assert::same($mqtt, $this->fileManager->read('MqttMessagingInteliGlue'));
	}

}

$test = new InteliGlueManagerTest($container);
$test->run();
