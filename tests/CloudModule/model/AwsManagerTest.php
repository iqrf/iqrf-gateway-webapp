<?php

/**
 * TEST: App\CloudModule\Model\AwsManager
 * @covers App\CloudModule\Model\AwsManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\ServiceModule\Model;

use App\CloudModule\Exception\InvalidPrivateKeyForCertificateException;
use App\CloudModule\Model\AwsManager;
use App\ConfigModule\Model\GenericManager;
use App\CoreModule\Model\CertificateManager;
use App\CoreModule\Model\JsonFileManager;
use App\CoreModule\Model\JsonSchemaManager;
use Nette\DI\Container;
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
	 * @var string Path to a directory with certificates and private keys
	 */
	private $certPath = __DIR__ . '/../../data/certificates/';

	/**
	 * @var string Path to a temporary directory with certificates and private keys
	 */
	private $certPathTemp = __DIR__ . '/../../temp/certificates/';

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
		$certManager = new CertificateManager();
		$schemaManager = new JsonSchemaManager($schemaPath);
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
		$this->mockedManager->createMqttInterface($this->formValues);
		Assert::same($mqtt, $this->fileManager->read('MqttMessagingAws'));
	}

	/**
	 * Test function to check a certificate and a private key (invalid private key)
	 */
	public function testCheckCertificateInvalid() {
		$array = $this->mockUploadedFiles($this->certPath);
		$pKeyFile = $this->certPath . 'pkey1.key';
		$pKeyValue = [
			'name' => 'pkey1.key',
			'type' => 'text/plain',
			'tmp_name' => $pKeyFile,
			'error' => UPLOAD_ERR_OK,
			'size' => filesize($pKeyFile),
		];
		$array['key'] = new FileUpload($pKeyValue);
		Assert::exception(function () use ($array) {
			$this->manager->checkCertificate($array);
		}, InvalidPrivateKeyForCertificateException::class);
	}

	/**
	 * Test function to check a certificate and a private key (valid private key)
	 */
	public function testCheckCertificateValid() {
		$array = $this->mockUploadedFiles($this->certPath);
		Assert::null($this->manager->checkCertificate($array));
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
		$certFile = $this->certPathTemp . 'cert0.pem';
		$pKeyFile = $this->certPathTemp . 'pkey0.key';
		FileSystem::copy($this->certPath . 'cert0.pem', $certFile);
		FileSystem::copy($this->certPath . 'pkey0.key', $pKeyFile);
		$array = $this->mockUploadedFiles($this->certPathTemp);
		$paths = [
			'cert' => $this->certPathTemp . 'cert.pem',
			'key' => $this->certPathTemp . 'pKey.key',
		];
		Assert::null($this->manager->uploadCertsAndKey($array, $paths));
	}

}

$test = new AwsManagerTest($container);
$test->run();
