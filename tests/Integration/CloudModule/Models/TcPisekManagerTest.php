<?php

/**
 * TEST: App\CloudModule\Models\TcPisekManager
 * @covers App\CloudModule\Models\TcPisekManager
 * @phpVersion >= 7.1
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Integration\CloudModule\Models;

use App\CloudModule\Models\TcPisekManager;
use GuzzleHttp\Client;
use Mockery;
use Mockery\MockInterface;
use Nette\Utils\FileSystem;
use Tester\Assert;
use Tests\Toolkit\TestCases\CloudIntegrationTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for TC Písek IoT platform manager
 */
class TcPisekManagerTest extends CloudIntegrationTestCase {

	/**
	 * @var TcPisekManager|MockInterface TC Písek IoT platform manager
	 */
	private $manager;

	/**
	 * @var array<string,mixed> Values from the configuration form
	 */
	private $values = [
		'broker' => 'connect.iot.tcpisek.cz',
		'username' => 'user',
		'password' => 'pass',
	];

	/**
	 * Tests the function to create a new MQTT interface
	 */
	public function testCreateMqttInterface(): void {
		$mqtt = [
			'component' => 'iqrf::MqttMessaging',
			'instance' => 'MqttMessagingTcPisek',
			'BrokerAddr' => 'ssl://connect.iot.tcpisek.cz:8883',
			'ClientId' => 'IqrfDpaMessaging1',
			'Persistence' => 1,
			'Qos' => 0,
			'TopicRequest' => 'Iqrf/DpaRequest',
			'TopicResponse' => 'Iqrf/DpaResponse',
			'User' => 'user',
			'Password' => 'pass',
			'EnabledSSL' => true,
			'KeepAliveInterval' => 20,
			'ConnectTimeout' => 5,
			'MinReconnect' => 1,
			'MaxReconnect' => 64,
			'TrustStore' => $this->certPath . 'tcPisek-ca.crt',
			'KeyStore' => '',
			'PrivateKey' => '',
			'PrivateKeyPassword' => '',
			'EnabledCipherSuites' => '',
			'EnableServerCertAuth' => false,
			'acceptAsyncMsg' => false,
		];
		$this->manager->createMqttInterface($this->values);
		Assert::same($mqtt, $this->fileManager->read('iqrf__MqttMessaging_TcPisek'));
	}

	/**
	 * Tests the function to download the root CA certificate
	 */
	public function testDownloadCaCertificate(): void {
		$expected = 'tcPisek-ca.crt';
		$manager = new TcPisekManager($this->certPath, $this->configManager, $this->mockHttpClient($expected));
		$manager->downloadCaCertificate();
		Assert::same($expected, FileSystem::read($this->certPath . $expected));
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$client = new Client();
		$this->manager = Mockery::mock(TcPisekManager::class, [$this->certPath, $this->configManager, $client])->makePartial();
		$this->manager->shouldReceive('downloadCaCertificate');
	}

}

$test = new TcPisekManagerTest();
$test->run();
