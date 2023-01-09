<?php

/**
 * TEST: App\CloudModule\Models\HexioManager
 * @covers App\CloudModule\Models\HexioManager
 * @phpVersion >= 7.4
 * @testCase
 */
/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
declare(strict_types = 1);

namespace Tests\Integration\CloudModule\Models;

use App\CloudModule\Models\HexioManager;
use GuzzleHttp\Client;
use Mockery;
use Nette\Utils\FileSystem;
use Tester\Assert;
use Tests\Toolkit\TestCases\CloudIntegrationTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for TC Písek IoT platform manager
 */
final class HexioManagerTest extends CloudIntegrationTestCase {

	/**
	 * @var HexioManager Hexio IoT platform manager
	 */
	private HexioManager $manager;

	/**
	 * @var array<string, string> Values from the configuration form
	 */
	private const VALUES = [
		'broker' => 'connect.hexio.cloud',
		'clientId' => 'IqrfDpaMessaging1',
		'topicRequest' => 'Iqrf/DpaRequest',
		'topicResponse' => 'Iqrf/DpaResponse',
		'username' => 'user',
		'password' => 'pass',
	];

	/**
	 * Tests the function to create a new MQTT interface
	 */
	public function testCreateMqttInterface(): void {
		$mqtt = [
			'component' => 'iqrf::MqttMessaging',
			'instance' => 'MqttMessagingHexio',
			'BrokerAddr' => 'ssl://connect.hexio.cloud:8883',
			'ClientId' => 'IqrfDpaMessaging1',
			'Persistence' => 1,
			'Qos' => 1,
			'TopicRequest' => '{no-process}/Iqrf/DpaRequest',
			'TopicResponse' => 'Iqrf/DpaResponse',
			'User' => 'user',
			'Password' => 'pass',
			'EnabledSSL' => true,
			'KeepAliveInterval' => 20,
			'ConnectTimeout' => 5,
			'MinReconnect' => 1,
			'MaxReconnect' => 64,
			'TrustStore' => $this->certPath . 'hexio-ca.crt',
			'KeyStore' => '',
			'PrivateKey' => '',
			'PrivateKeyPassword' => '',
			'EnabledCipherSuites' => '',
			'EnableServerCertAuth' => false,
			'acceptAsyncMsg' => false,
		];
		$this->manager->createMqttInterface(self::VALUES);
		Assert::same($mqtt, $this->fileManager->readJson('iqrf__MqttMessaging_Hexio.json'));
	}

	/**
	 * Tests the function to download the root CA certificate
	 */
	public function testDownloadCaCertificate(): void {
		$expected = 'hexio-ca.crt';
		$manager = new HexioManager($this->certPath, $this->configManager, $this->mockHttpClient($expected));
		$manager->downloadCaCertificate();
		Assert::same($expected, FileSystem::read($this->certPath . $expected));
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$client = new Client();
		$this->manager = Mockery::mock(HexioManager::class, [$this->certPath, $this->configManager, $client])->makePartial();
		$this->manager->shouldReceive('downloadCaCertificate');
	}

}

$test = new HexioManagerTest();
$test->run();
