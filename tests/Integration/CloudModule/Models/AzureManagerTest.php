<?php

/**
 * TEST: App\CloudModule\Models\AzureManager
 * @covers App\CloudModule\Models\AzureManager
 * @phpVersion >= 7.4
 * @testCase
 */
/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
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

use App\CloudModule\Exceptions\InvalidConnectionStringException;
use App\CloudModule\Models\AzureManager;
use DateTime;
use Mockery;
use Mockery\MockInterface;
use Tester\Assert;
use Tests\Toolkit\TestCases\CloudIntegrationTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Test for MS Azure IoT Hub manager
 */
final class AzureManagerTest extends CloudIntegrationTestCase {

	/**
	 * MS Azure IoT Hub connection string for the device
	 */
	private const CONNECTION_STRING = 'HostName=iqrf.azure-devices.net;DeviceId=IQRFGW;SharedAccessKey=1234567890abcdefghijklmnopqrstuvwxyzABCDEFG=';

	/**
	 * @var AzureManager|MockInterface Microsoft Azure IoT Hub manager
	 */
	private AzureManager|MockInterface $manager;

	/**
	 * Tests the function to create MQTT interface
	 */
	public function testCreateMqttInterface(): void {
		$mqtt = [
			'component' => 'iqrf::MqttMessaging',
			'instance' => 'MqttMessagingAzure',
			'BrokerAddr' => 'ssl://iqrf.azure-devices.net:8883',
			'ClientId' => 'IQRFGW',
			'Persistence' => 1,
			'Qos' => 0,
			'TopicRequest' => 'devices/IQRFGW/messages/devicebound/#',
			'TopicResponse' => 'devices/IQRFGW/messages/events/',
			'User' => 'iqrf.azure-devices.net/IQRFGW',
			'Password' => 'generatedSasToken',
			'KeepAliveInterval' => 20,
			'ConnectTimeout' => 5,
			'MinReconnect' => 1,
			'MaxReconnect' => 64,
			'TrustStore' => '',
			'KeyStore' => '',
			'PrivateKey' => '',
			'PrivateKeyPassword' => '',
			'EnabledCipherSuites' => '',
			'EnableServerCertAuth' => false,
			'acceptAsyncMsg' => false,
		];
		$array = ['connectionString' => self::CONNECTION_STRING];
		$this->manager->shouldReceive('generateSasToken')->andReturn('generatedSasToken');
		$this->manager->createMqttInterface($array);
		Assert::same($mqtt, $this->fileManager->readJson('iqrf__MqttMessaging_Azure.json'));
	}

	/**
	 * Tests the function to check the connection string (invalid connection string)
	 */
	public function testCheckConnectionStringInvalid(): void {
		$invalidString = 'HostName=iqrf.azure-devices.net;SharedAccessKeyName=iothubowner;SharedAccessKey=1234567890abcdefghijklmnopqrstuvwxyzABCDEFG=';
		Assert::exception(function () use ($invalidString): void {
			$this->manager->checkConnectionString($invalidString);
		}, InvalidConnectionStringException::class);
	}

	/**
	 * Tests the function to check the connection string (valid connection string)
	 */
	public function testCheckConnectionStringValid(): void {
		Assert::noError(function (): void {
			$this->manager->checkConnectionString(self::CONNECTION_STRING);
		});
	}

	/**
	 * Tests the function to generate a shared access signature token
	 */
	public function testGenerateSasToken(): void {
		$resourceUri = 'iqrf.azure-devices.net/devices/iqrfGwTest';
		$signingKey = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFG';
		$policyName = null;
		$expiresInMins = intdiv((new DateTime('2018-05-10T11:00:00'))->getTimestamp(), 60) -
			intdiv((new DateTime())->getTimestamp(), 60) + 5_256_000;
		$actual = $this->manager->generateSasToken($resourceUri, $signingKey, $policyName, $expiresInMins);
		$expected = 'SharedAccessSignature sr=iqrf.azure-devices.net%2Fdevices%2FiqrfGwTest&sig=loSMVo4aSTBFh6psEwJcSInBGo%2BSD3noiFSHbgQuSMo%3D&se=1841302800';
		Assert::same($expected, $actual);
	}

	/**
	 * Tests the function to parse the connection string
	 */
	public function testParseConnectionString(): void {
		$expected = [
			'HostName' => 'iqrf.azure-devices.net',
			'DeviceId' => 'IQRFGW',
			'SharedAccessKey' => '1234567890abcdefghijklmnopqrstuvwxyzABCDEFG',
		];
		Assert::same($expected, $this->manager->parseConnectionString(self::CONNECTION_STRING));
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$this->manager = Mockery::mock(AzureManager::class, [$this->configManager])->makePartial();
	}

}

$test = new AzureManagerTest();
$test->run();
