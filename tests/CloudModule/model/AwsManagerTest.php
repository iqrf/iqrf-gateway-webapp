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

class AwsManagerTest extends TestCase {

	/**
	 * @var CertificateManager Certificate manager
	 */
	private $certManager;

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
	 * @var \Mockery\Mock Mocked Amazon AWS IoT manager
	 */
	private $manager;

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
		$this->certManager = new CertificateManager();
		$schemaManager = new JsonSchemaManager($this->schemaPath);
		$this->configManager = new GenericManager($this->fileManager, $schemaManager);
		$this->manager = \Mockery::mock(AwsManager::class, [$this->certManager, $this->configManager])->makePartial();
		$this->manager->shouldReceive('downloadCaCertificate')->andReturn(null);
		$this->manager->shouldReceive('checkCertificate')->andReturn(null);
		$this->manager->shouldReceive('uploadCertsAndKey')->andReturn(null);
	}

	/**
	 * @test
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
		$this->manager->createMqttInterface($values);
		Assert::same($mqtt, $this->fileManager->read('MqttMessagingAws'));
	}

	/**
	 * @test
	 * Test function to check a certificate and a private key
	 */
	public function testCheckCertificate() {
		$manager = new AwsManager($this->certManager, $this->configManager);
		$certFile = __DIR__ . '/../../model/certs/cert0.pem';
		$certValue = [
			'name' => 'cert0.pem',
			'type' => 'text/plain',
			'tmp_name' => $certFile,
			'error' => UPLOAD_ERR_OK,
			'size' => filesize($certFile),
		];
		$pKeyFile = __DIR__ . '/../../model/certs/pkey0.key';
		$pKeyValue = [
			'name' => 'pkey0.key',
			'type' => 'text/plain',
			'tmp_name' => $pKeyFile,
			'error' => UPLOAD_ERR_OK,
			'size' => filesize($pKeyFile),
		];
		$array['cert'] = new FileUpload($certValue);
		$array['key'] = new FileUpload($pKeyValue);
		Assert::null($manager->checkCertificate(ArrayHash::from($array)));
		Assert::exception(function () use ($manager, $array) {
			$pKeyFile = __DIR__ . '/../../model/certs/pkey1.key';
			$pKeyValue = [
				'name' => 'pkey1.key',
				'type' => 'text/plain',
				'tmp_name' => $pKeyFile,
				'error' => UPLOAD_ERR_OK,
				'size' => filesize($pKeyFile),
			];
			$array['key'] = new FileUpload($pKeyValue);
			$manager->checkCertificate(ArrayHash::from($array));
		}, InvalidPrivateKeyForCertificate::class);
	}

	/**
	 * @test
	 * Test function to create paths for certificates
	 */
	public function testCreatePaths() {
		$timestamp = (new \DateTime())->format(\DateTime::ISO8601);
		$actual = $this->manager->createPaths();
		$paths = [
			'cert' => '/etc/iqrf-daemon/certs/' . $timestamp . '-aws.crt',
			'key' => '/etc/iqrf-daemon/certs/' . $timestamp . '-aws.key',
		];
		Assert::same($paths, $actual);
	}

	/**
	 * @test
	 * Test function to upload root CA certificate, certificate and private key
	 */
	public function testUploadCertsAndKey() {
		$manager = new AwsManager($this->certManager, $this->configManager);
		$certFile = __DIR__ . '/certs/cert0.pem';
		$pKeyFile = __DIR__ . '/certs/pkey0.key';
		FileSystem::copy(__DIR__ . '/../../model/certs/cert0.pem', $certFile);
		FileSystem::copy(__DIR__ . '/../../model/certs/pkey0.key', $pKeyFile);
		$certValue = [
			'name' => 'cert0.pem',
			'type' => 'text/plain',
			'tmp_name' => $certFile,
			'error' => UPLOAD_ERR_OK,
			'size' => filesize($certFile),
		];
		$pKeyValue = [
			'name' => 'pkey0.key',
			'type' => 'text/plain',
			'tmp_name' => $pKeyFile,
			'error' => UPLOAD_ERR_OK,
			'size' => filesize($pKeyFile),
		];
		$array['cert'] = new FileUpload($certValue);
		$array['key'] = new FileUpload($pKeyValue);
		$paths = [
			'cert' => __DIR__ . '/certs/cert.pem',
			'key' => __DIR__ . '/certs/pKey.key',
		];
		Assert::null($manager->uploadCertsAndKey(ArrayHash::from($array), $paths));
	}

}

$test = new AwsManagerTest($container);
$test->run();
