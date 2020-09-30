<?php

/**
 * TEST: App\CloudModule\Models\AwsManager
 * @covers App\CloudModule\Models\AwsManager
 * @phpVersion >= 7.2
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Integration\CloudModule\Models;

use App\CloudModule\Exceptions\InvalidPrivateKeyForCertificateException;
use App\CloudModule\Models\AwsManager;
use App\CoreModule\Models\CertificateManager;
use DateTime;
use GuzzleHttp\Client;
use Mockery;
use Mockery\Mock;
use Nette\Http\FileUpload;
use Nette\Utils\FileSystem;
use Tester\Assert;
use Tests\Toolkit\TestCases\CloudIntegrationTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for Amazon AWS IoT
 */
final class AwsManagerTest extends CloudIntegrationTestCase {

	/**
	 * @var CertificateManager Certificate manager
	 */
	private $certManager;

	/**
	 * @var string Path to a directory with certificates and private keys
	 */
	protected $certPathReal;

	/**
	 * @var array<string> Values from Amazon AWS IoT form
	 */
	private $formValues = [
		'endpoint' => 'localhost',
	];

	/**
	 * @var Mock|AwsManager Amazon AWS IoT manager
	 */
	private $manager;

	/**
	 * Tests the function to create a new MQTT interface
	 */
	public function testCreateMqttInterface(): void {
		$timestamp = (new DateTime())->format(DateTime::ISO8601);
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
			'TrustStore' => $this->certPath . 'aws-ca.crt',
			'KeyStore' => $this->certPath . $timestamp . '-aws.crt',
			'PrivateKey' => $this->certPath . $timestamp . '-aws.key',
			'PrivateKeyPassword' => '',
			'EnabledCipherSuites' => '',
			'EnableServerCertAuth' => false,
			'acceptAsyncMsg' => false,
		];
		$client = new Client();
		$manager = Mockery::mock(AwsManager::class, [$this->certPath, $this->certManager, $this->configManager, $client])->makePartial();
		$manager->shouldReceive('downloadCaCertificate')->andReturn(null);
		$manager->shouldReceive('checkCertificate')->andReturn(null);
		$manager->shouldReceive('uploadCertsAndKey')->andReturn(null);
		$manager->createMqttInterface($this->formValues);
		Assert::same($mqtt, $this->fileManager->read('iqrf__MqttMessaging_Aws'));
	}

	/**
	 * Tests the function to check a certificate and a private key (invalid private key)
	 */
	public function testCheckCertificateInvalid(): void {
		Assert::exception(function (): void {
			$certificate = FileSystem::read($this->certPathReal . 'cert0.pem');
			$privateKey = FileSystem::read($this->certPathReal . 'pkey1.key');
			$this->manager->checkCertificate($certificate, $privateKey);
		}, InvalidPrivateKeyForCertificateException::class);
	}

	/**
	 * Tests the function to check a certificate and a private key (valid private key)
	 */
	public function testCheckCertificateValid(): void {
		Assert::noError(function (): void {
			$certificate = FileSystem::read($this->certPathReal . 'cert0.pem');
			$privateKey = FileSystem::read($this->certPathReal . 'pkey0.key');
			$this->manager->checkCertificate($certificate, $privateKey);
		});
	}

	/**
	 * Tests the function to create paths for certificates
	 */
	public function testCreatePaths(): void {
		$timestamp = (new DateTime())->format(DateTime::ISO8601);
		$actual = $this->manager->createPaths();
		$paths = [
			'cert' => $this->certPath . $timestamp . '-aws.crt',
			'key' => $this->certPath . $timestamp . '-aws.key',
		];
		Assert::same($paths, $actual);
	}

	/**
	 * Tests the function to upload the certificate and the private key
	 */
	public function testUploadCertsAndKey(): void {
		$paths = [
			'cert' => $this->certPath . 'cert.pem',
			'key' => $this->certPath . 'pKey.key',
		];
		Assert::noError(function () use ($paths): void {
			$certificate = FileSystem::read($this->certPathReal . 'cert0.pem');
			$privateKey = FileSystem::read($this->certPathReal . 'pkey0.key');
			$this->manager->uploadCertsAndKey($paths, $certificate, $privateKey);
		});
	}

	/**
	 * Tests the function to download the root CA certificate
	 */
	public function testDownloadCaCertificate(): void {
		$expected = 'aws-ca.crt';
		$manager = new AwsManager($this->certPath, $this->certManager, $this->configManager, $this->mockHttpClient($expected));
		$manager->downloadCaCertificate();
		Assert::same($expected, FileSystem::read($this->certPath . $expected));
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$this->certPathReal = realpath(__DIR__ . '/../../../data/certificates/') . '/';
		$this->certManager = new CertificateManager();
		$client = new Client();
		$this->manager = new AwsManager($this->certPath, $this->certManager, $this->configManager, $client);
		$this->formValues = array_merge($this->formValues, $this->mockUploadedFiles($this->certPathReal));
	}

	/**
	 * Mocks uploaded certificate and private key
	 * @param string $path Path to certificate and private key
	 * @return array<FileUpload> Mocked uploaded certificate and private key
	 */
	private function mockUploadedFiles(string $path): array {
		$certFile = $path . '/cert0.pem';
		$pKeyFile = $path . '/pkey0.key';
		return [
			'certificate' => FileSystem::read($certFile),
			'privateKey' => FileSystem::read($pKeyFile),
		];
	}

}

$test = new AwsManagerTest();
$test->run();
