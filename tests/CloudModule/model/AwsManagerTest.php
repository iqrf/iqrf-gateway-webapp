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
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
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
	 * @var CertificateManager Certificate manager
	 */
	private $certManager;

	/**
	 * @var string Path to a directory with certificates and private keys
	 */
	private $certPath;

	/**
	 * @var string Path to a temporary directory with certificates and private keys
	 */
	private $certPathTemp;

	/**
	 * @var GenericManager Generic configuration manager
	 */
	private $configManager;

	/**
	 * @var JsonFileManager JSON file manager
	 */
	private $fileManager;

	/**
	 * @var AwsManager Amazon AWS IoT manager
	 */
	private $manager;

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
		$this->certPath = realpath(__DIR__ . '/../../data/certificates/') . '/';
		$this->certPathTemp = realpath(__DIR__ . '/../../temp/certificates/') . '/';
	}

	/**
	 * Test function to create MQTT interface
	 */
	public function testCreateMqttInterface(): void {
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
			'TrustStore' => $this->certPathTemp . 'aws-ca.crt',
			'KeyStore' => $this->certPathTemp . $timestamp . '-aws.crt',
			'PrivateKey' => $this->certPathTemp . $timestamp . '-aws.key',
			'PrivateKeyPassword' => '',
			'EnabledCipherSuites' => '',
			'EnableServerCertAuth' => false,
			'acceptAsyncMsg' => false,
		];
		$client = new Client();
		$manager = \Mockery::mock(AwsManager::class, [$this->certPathTemp, $this->certManager, $this->configManager, $client])->makePartial();
		$manager->shouldReceive('downloadCaCertificate')->andReturn(null);
		$manager->shouldReceive('checkCertificate')->andReturn(null);
		$manager->shouldReceive('uploadCertsAndKey')->andReturn(null);
		$manager->createMqttInterface($this->formValues);
		Assert::same($mqtt, $this->fileManager->read('iqrf__MqttMessaging_Aws'));
	}

	/**
	 * Test function to check a certificate and a private key (invalid private key)
	 */
	public function testCheckCertificateInvalid(): void {
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
	 * Mock uploaded certificate and private key
	 * @param string $path Path to certificate and private key
	 * @return array Mocked uploaded certificate and private key
	 */
	private function mockUploadedFiles(string $path) {
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
		return [
			'cert' => new FileUpload($certValue),
			'key' => new FileUpload($pKeyValue),
		];
	}

	/**
	 * Test function to check a certificate and a private key (valid private key)
	 */
	public function testCheckCertificateValid(): void {
		$array = $this->mockUploadedFiles($this->certPath);
		Assert::noError(function () use ($array) {
			$this->manager->checkCertificate($array);
		});
	}

	/**
	 * Test function to create paths for certificates
	 */
	public function testCreatePaths(): void {
		$timestamp = (new \DateTime())->format(\DateTime::ISO8601);
		$actual = $this->manager->createPaths();
		$paths = [
			'cert' => $this->certPathTemp . $timestamp . '-aws.crt',
			'key' => $this->certPathTemp . $timestamp . '-aws.key',
		];
		Assert::same($paths, $actual);
	}

	/**
	 * Test function to upload root CA certificate, certificate and private key
	 */
	public function testUploadCertsAndKey(): void {
		$certFile = $this->certPathTemp . 'cert0.pem';
		$pKeyFile = $this->certPathTemp . 'pkey0.key';
		FileSystem::copy($this->certPath . 'cert0.pem', $certFile);
		FileSystem::copy($this->certPath . 'pkey0.key', $pKeyFile);
		$array = $this->mockUploadedFiles($this->certPathTemp);
		$paths = [
			'cert' => $this->certPathTemp . 'cert.pem',
			'key' => $this->certPathTemp . 'pKey.key',
		];
		Assert::noError(function () use ($array, $paths) {
			$this->manager->uploadCertsAndKey($array, $paths);
		});
	}

	/**
	 * Test function to download root CA certificate
	 */
	public function testDownloadCaCertificate(): void {
		$expected = 'aws-ca.crt';
		$responseMock = new MockHandler([
			new Response(200, [], $expected),
		]);
		$handler = HandlerStack::create($responseMock);
		$client = new Client(['handler' => $handler]);
		$manager = new AwsManager($this->certPathTemp, $this->certManager, $this->configManager, $client);
		$manager->downloadCaCertificate();
		Assert::same($expected, FileSystem::read($this->certPathTemp . $expected));
	}

	/**
	 * Set up the test environment
	 */
	protected function setUp(): void {
		$configPath = __DIR__ . '/../../temp/configuration/';
		$schemaPath = __DIR__ . '/../../data/cfgSchemas/';
		$this->fileManager = new JsonFileManager($configPath);
		$this->certManager = new CertificateManager();
		$schemaManager = new JsonSchemaManager($schemaPath);
		$this->configManager = new GenericManager($this->fileManager, $schemaManager);
		$client = new Client();
		$this->manager = new AwsManager($this->certPathTemp, $this->certManager, $this->configManager, $client);
	}

	/**
	 * Cleanup the test environment
	 */
	protected function tearDown(): void {
		\Mockery::close();
	}

}

$test = new AwsManagerTest($container);
$test->run();
