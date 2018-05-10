<?php

/**
 * TEST: App\CloudModule\Model\AzureManager
 * @covers App\CloudModule\Model\AzureManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types=1);

namespace Test\ServiceModule\Model;

use App\CloudModule\Model\AzureManager;
use App\CloudModule\Model\InvalidConnectionString;
use Nette\DI\Container;
use Nette\Utils\ArrayHash;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

class AzureManagerTest extends TestCase {

	/**
	 * @var Container Nette Tester Container
	 */
	private $container;

	/**
	 * @var AzureManager MS Azure IoT hub manager
	 */
	private $manager;

	/**
	 * @var string MS Azure IoT Hub connection string for the device
	 */
	private $connectionString = 'HostName=iqrf.azure-devices.net;DeviceId=IQRFGW;SharedAccessKey=1234567890abcdefghijklmnopqrstuvwxyzABCDEFG=';

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
		$this->manager = \Mockery::mock(AzureManager::class)->makePartial();
		$this->manager->shouldReceive('generateSasToken')->andReturn('generatedSasToken');
	}

	/**
	 * @test
	 * Test function to create MQTT interface
	 */
	public function testCreateMqttInterface() {
		$mqtt = [
			'Name' => 'MqttMessagingAzure',
			'Enabled' => true,
			'BrokerAddr' => 'ssl://iqrf.azure-devices.net:8883',
			'ClientId' => 'IQRFGW',
			'Persistence' => 1,
			'Qos' => 0,
			'TopicRequest' => 'devices/IQRFGW/messages/devicebound/#',
			'TopicResponse' => 'devices/IQRFGW/messages/events/',
			'User' => 'iqrf.azure-devices.net/IQRFGW',
			'Password' => 'generatedSasToken',
			'EnabledSSL' => true,
			'KeepAliveInterval' => 20,
			'ConnectTimeout' => 5,
			'MinReconnect' => 1,
			'MaxReconnect' => 64,
			'TrustStore' => '',
			'KeyStore' => '',
			'PrivateKey' => '',
			'PrivateKeyPassword' => '',
			'EnabledCipherSuites' => '',
			'EnableServerCertAuth' => false
		];
		Assert::same($mqtt, iterator_to_array($this->manager->createMqttInterface($this->connectionString)));
	}

	/**
	 * @test
	 * Test function to create Base service
	 */
	public function testCreateBaseService() {
		$mqtt = [
			'Name' => 'BaseServiceForMQTTAzure',
			'Messaging' => 'MqttMessagingAzure',
			'Serializers' => ['JsonSerializer'],
			'Properties' => ['AsyncDpaMessage' => true],
		];
		$actual = $this->manager->createBaseService();
		Assert::same($mqtt['Serializers'], iterator_to_array($actual['Serializers']));
		Assert::same($mqtt['Properties'], iterator_to_array($actual['Properties']));
		unset($actual['Serializers'], $actual['Properties'], $mqtt['Serializers'], $mqtt['Properties']);
		Assert::same($mqtt, iterator_to_array($actual));
	}

	/**
	 * @test
	 * Test function to check the connection string
	 */
	public function testCheckConnectionString() {
		Assert::null($this->manager->checkConnectionString($this->connectionString));
		$invalidString = 'HostName=iqrf.azure-devices.net;SharedAccessKeyName=iothubowner;SharedAccessKey=1234567890abcdefghijklmnopqrstuvwxyzABCDEFG=';
		Assert::exception(function() use ($invalidString) {
			$this->manager->checkConnectionString($invalidString);
		}, InvalidConnectionString::class);
	}

	/**
	 * @test
	 * Test function to generate shared access signature token
	 */
	public function testGenerateSasToken() {
		$resourceUri = 'iqrf.azure-devices.net/devices/iqrfGwTest';
		$signingKey = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFG';
		$policyName = null;
		$expiresInMins = intdiv((new \DateTime('2018-05-10T11:00:00'))->getTimestamp(), 60) -
				intdiv((new \DateTime())->getTimestamp(), 60) + 5256000;
		$manager = new AzureManager();
		$actual = $manager->generateSasToken($resourceUri, $signingKey, $policyName, $expiresInMins);
		$expected = 'SharedAccessSignature sr=iqrf.azure-devices.net%2Fdevices%2FiqrfGwTest&sig=loSMVo4aSTBFh6psEwJcSInBGo%2BSD3noiFSHbgQuSMo%3D&se=1841302800';
		Assert::same($expected, $actual);
	}

	/**
	 * @test
	 * Test function to parse the connection string
	 */
	public function testParseConnectionString() {
		$expected = [
			'HostName' => 'iqrf.azure-devices.net',
			'DeviceId' => 'IQRFGW',
			'SharedAccessKey' => '1234567890abcdefghijklmnopqrstuvwxyzABCDEFG',
		];
		Assert::same($expected, $this->manager->parseConnectionString($this->connectionString));
	}

}

$test = new AzureManagerTest($container);
$test->run();
