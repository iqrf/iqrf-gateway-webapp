<?php

/**
 * TEST: App\CloudModule\Model\AwsManager
 * @covers App\CloudModule\Model\AwsManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\ServiceModule\Model;

use App\CloudModule\Model\AwsManager;
use App\CloudModule\Model\InvalidPrivateKeyForCertificate;
use App\ConfigModule\Model\GenericManager;
use App\Model\CertificateManager;
use App\Model\JsonFileManager;
use App\Model\JsonSchemaManager;
use Nette\DI\Container;
use Nette\Utils\ArrayHash;
use Nette\Http\FileUpload;
use Nette\Utils\FileSystem;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for Amazon AWS IoT
 */
class AwsManagerTest extends TestCase {

	/**
	 * @var Container Nette Tester Container
	 */
	private $container;

	/**
	 * @var JsonFileManager JSON file manager
	 */
	private $fileManager;

	/**
	 * @var AwsManager Amazon AWS IoT manager
	 */
	private $manager;

	/**
	 * @var \Mockery\Mock Mocked Amazon AWS IoT manager
	 */
	private $mockedManager;

	/**
	 * @var array Values from Amazon AWS IoT form
	 */
	private $formValues = [
		'endpoint' => 'localhost',
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
		$certManager = new CertificateManager();
		$schemaManager = new JsonSchemaManager($this->schemaPath);
		$configManager = new GenericManager($this->fileManager, $schemaManager);
		$this->manager = new AwsManager($certManager, $configManager);
		$this->mockedManager = \Mockery::mock(AwsManager::class, [$certManager, $configManager])->makePartial();
		$this->mockedManager->shouldReceive('downloadCaCertificate')->andReturn(null);
		$this->mockedManager->shouldReceive('checkCertificate')->andReturn(null);
		$this->mockedManager->shouldReceive('uploadCertsAndKey')->andReturn(null);
	}

	/**
	 * Mock uploaded certificate and private key
	 * @param string $path Path to certificate and private key
	 * @return array Mocked uploaded certificate and private key
	 */
	public function mockUploadedFiles(string $path) {
		$certFile = $path . '/cert0.pem';
		$certValue = [
			'name' => 'cert0.pem',
			'type' => 'text/plain',
			'tmp_name' => $certFile,
			'error' => UPLOAD_ERR_OK,
			'size' => filesize($certFile),
		];
		$pKeyFile = $path . '/pkey0.key';
		$pKeyValue = [
			'name' => 'pkey0.key',
			'type' => 'text/plain',
			'tmp_name' => $pKeyFile,
			'error' => UPLOAD_ERR_OK,
			'size' => filesize($pKeyFile),
		];
		$array['cert'] = new FileUpload($certValue);
		$array['key'] = new FileUpload($pKeyValue);
		return $array;
	}

	/**
	 * Test function to create MQTT interface
	 */
	public function testCreateMqttInterface() {
		$timestamp = (new \DateTime())->format(\DateTime::ISO8601);
		$values = ArrayHash::from($this->formValues);
		$mqtt = [
			'component' => 'iqrf::MqttMessaging',
			'instance' => 'MqttMessagingAws',
			'BrokerAddr' => 'ssl://localhost:8883',
			'ClientId' => 'IqrfDpaMessaging1',
			'Persistence' => 1,
			'Qos' => 0,
			'TopicRequest' => 'Iqrf/DpaRequest',
			'TopicResponse' => 'Iqrf/DpaResponse',
			'User' => '',
			'Password' => '',
			'EnabledSSL' => true,
			'KeepAliveInterval' => 20,
			'ConnectTimeout' => 5,
			'MinReconnect' => 1,
			'MaxReconnect' => 64,
			'TrustStore' => '/etc/iqrf-daemon/certs/aws-ca.crt',
			'KeyStore' => '/etc/iqrf-daemon/certs/' . $timestamp . '-aws.crt',
			'PrivateKey' => '/etc/iqrf-daemon/certs/' . $timestamp . '-aws.key',
			'PrivateKeyPassword' => '',
			'EnabledCipherSuites' => '',
			'EnableServerCertAuth' => false,
			'acceptAsyncMsg' => false,
		];
		$this->mockedManager->createMqttInterface($values);
		Assert::same($mqtt, $this->fileManager->read('MqttMessagingAws'));
	}

	/**
	 * Test function to check a certificate and a private key
	 */
	public function testCheckCertificate() {
		$array = $this->mockUploadedFiles(__DIR__ . '/../../model/certs/');
		Assert::null($this->manager->checkCertificate(ArrayHash::from($array)));
		Assert::exception(function () use ($array) {
			$pKeyFile = __DIR__ . '/../../model/certs/pkey1.key';
			$pKeyValue = [
				'name' => 'pkey1.key',
				'type' => 'text/plain',
				'tmp_name' => $pKeyFile,
				'error' => UPLOAD_ERR_OK,
				'size' => filesize($pKeyFile),
			];
			$array['key'] = new FileUpload($pKeyValue);
			$this->manager->checkCertificate(ArrayHash::from($array));
		}, InvalidPrivateKeyForCertificate::class);
	}

	/**
	 * Test function to create paths for certificates
	 */
	public function testCreatePaths() {
		$timestamp = (new \DateTime())->format(\DateTime::ISO8601);
		$actual = $this->mockedManager->createPaths();
		$paths = [
			'cert' => '/etc/iqrf-daemon/certs/' . $timestamp . '-aws.crt',
			'key' => '/etc/iqrf-daemon/certs/' . $timestamp . '-aws.key',
		];
		Assert::same($paths, $actual);
	}

	/**
	 * Test function to upload root CA certificate, certificate and private key
	 */
	public function testUploadCertsAndKey() {
		$certFile = __DIR__ . '/certs/cert0.pem';
		$pKeyFile = __DIR__ . '/certs/pkey0.key';
		FileSystem::copy(__DIR__ . '/../../model/certs/cert0.pem', $certFile);
		FileSystem::copy(__DIR__ . '/../../model/certs/pkey0.key', $pKeyFile);
		$array = $this->mockUploadedFiles(__DIR__ . '/certs/');
		$paths = [
			'cert' => __DIR__ . '/certs/cert.pem',
			'key' => __DIR__ . '/certs/pKey.key',
		];
		Assert::null($this->manager->uploadCertsAndKey(ArrayHash::from($array), $paths));
	}

}

$test = new AwsManagerTest($container);
$test->run();
