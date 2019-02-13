<?php

/**
 * TEST: App\CloudModule\Models\InteliGlueManager
 * @covers App\CloudModule\Models\InteliGlueManager
 * @phpVersion >= 7.1
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Integration\ServiceModule\Models;

use App\CloudModule\Models\InteliGlueManager;
use GuzzleHttp\Client;
use Mockery;
use Mockery\Mock;
use Nette\Utils\FileSystem;
use Tester\Assert;
use Tests\Toolkit\TestCases\CloudIntegrationTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for Inteliment InteliGlue manager
 */
class InteliGlueManagerTest extends CloudIntegrationTestCase {

	/**
	 * @var mixed[] Values from Inteliments InteliGlue form
	 */
	private $formValues = [
		'assignedPort' => 1234,
		'clientId' => 'client1234',
		'password' => 'pass1234',
		'rootTopic' => 'root',
	];

	/**
	 * @var Mock|InteliGlueManager Inteliments InteliGlue manager
	 */
	private $manager;

	/**
	 * Tests the function to create a new MQTT interface
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
	 * Tests the function to download the root CA certificate
	 */
	public function testDownloadCaCertificate(): void {
		$expected = 'inteliments-ca.crt';
		$manager = new InteliGlueManager($this->certPath, $this->configManager, $this->mockHttpClient($expected));
		$manager->downloadCaCertificate();
		Assert::same($expected, FileSystem::read($this->certPath . $expected));
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$client = new Client();
		$this->manager = Mockery::mock(InteliGlueManager::class, [$this->certPath, $this->configManager, $client])->makePartial();
		$this->manager->shouldReceive('downloadCaCertificate')->andReturn(null);
	}

}

$test = new InteliGlueManagerTest();
$test->run();
