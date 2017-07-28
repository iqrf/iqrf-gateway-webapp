<?php

/**
 * TEST: App\Model\ConfigManager
 * @phpVersion >= 5.6
 * @testCase
 */

namespace Test\Model;

use App\Model\ConfigManager;
use App\Model\ConfigParser;
use Nette\DI\Container;
use Nette\Utils\ArrayHash;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../bootstrap.php';

class ConfigManagerTest extends TestCase {

	private $container;

	function __construct(Container $container) {
		$this->container = $container;
	}

	/**
	 * @test
	 * Test function to read configuration file
	 */
	public function testRead() {
		$path = __DIR__ . '/../configuration/';
		$configParser = new ConfigParser();
		$configManager = new ConfigManager($path, $configParser);
		$expected = Json::decode(FileSystem::read(__DIR__ . '/../configuration/config.json'), Json::FORCE_ARRAY);
		Assert::equal($expected, $configManager->read('config'));
	}

	/**
	 * @test
	 * Test function to write configuration file
	 */
	public function testWrite() {
		$path = __DIR__ . '/../configuration-test/';
		$fileName = 'MqMessaging-test';
		$configParser = new ConfigParser();
		$configManager = new ConfigManager($path, $configParser);
		$expected = Json::decode(FileSystem::read(__DIR__ . '/../configuration/MqMessaging.json'), Json::FORCE_ARRAY);
		$configManager->write($fileName, $expected);
		Assert::equal($expected, $configManager->read($fileName));
	}

	/**
	 * @test
	 * Test function to parse configuration of Base services
	 */
	public function testSaveBaseService() {
		$path = __DIR__ . '/../configuration-test/';
		$fileName = 'BaseService';
		$configParser = new ConfigParser();
		$configManager = new ConfigManager($path, $configParser);
		$array = [
			"Name" => "BaseServiceForMQ",
			"Messaging" => "MqMessaging",
			"Serializers" => [
				"JsonSerializer"
			],
			"Properties" => [
			]
		];
		$expected = Json::decode(FileSystem::read(__DIR__ . '/../configuration/BaseService.json'), Json::FORCE_ARRAY);
		$configManager->write($fileName, $expected);
		$expected['Instances'][0]['Serializers'] = ['JsonSerializer'];
		$configManager->saveBaseService(ArrayHash::from($array), 0);
		Assert::equal($expected, $configManager->read($fileName));
	}

	/**
	 * @test
	 * Test function to save configuration of Components
	 */
	public function testSaveComponents() {
		$path = __DIR__ . '/../configuration-test/';
		$configParser = new ConfigParser();
		$configManager = new ConfigManager($path, $configParser);
		$array = [
			"TracerFile" => false,
			"IqrfInterface" => true,
			"UdpMessaging" => true,
			"MqttMessaging" => true,
			"MqMessaging" => true,
			"Scheduler" => true,
			"SimpleSerializer" => true,
			"JsonSerializer" => true,
			"BaseService" => true
		];
		$expected = Json::decode(FileSystem::read(__DIR__ . '/../configuration/config.json'), Json::FORCE_ARRAY);
		$configManager->write('config', $expected);
		$expected['Components'][0]['Enabled'] = false;
		$configManager->saveComponents(ArrayHash::from($array));
		Assert::equal($expected, $configManager->read('config'));
	}

	/**
	 * @test
	 * Test function to parse configuration of Instances
	 */
	public function testSaveInstances() {
		$path = __DIR__ . '/../configuration-test/';
		$fileName = 'MqttMessaging';
		$configParser = new ConfigParser();
		$configManager = new ConfigManager($path, $configParser);
		$array = [
			"Name" => "MqttMessaging2",
			"Enabled" => false,
			"BrokerAddr" => "tcp://iot.eclipse.org:1883",
			"ClientId" => "IqrfDpaMessaging2",
			"Persistence" => 1,
			"Qos" => 1,
			"TopicRequest" => "Iqrf/DpaRequest",
			"TopicResponse" => "Iqrf/DpaResponse",
			"User" => "",
			"Password" => "",
			"EnabledSSL" => true,
			"KeepAliveInterval" => 20,
			"ConnectTimeout" => 5,
			"MinReconnect" => 1,
			"MaxReconnect" => 64,
			"TrustStore" => "server-ca.crt",
			"KeyStore" => "client.pem",
			"PrivateKey" => "client-privatekey.pem",
			"PrivateKeyPassword" => "",
			"EnabledCipherSuites" => "",
			"EnableServerCertAuth" => true
		];
		$expected = Json::decode(FileSystem::read(__DIR__ . '/../configuration/MqttMessaging.json'), Json::FORCE_ARRAY);
		$configManager->write($fileName, $expected);
		$expected['Instances'][1]['Properties']['EnabledSSL'] = true;
		$configManager->saveInstances($fileName, ArrayHash::from($array), 1);
		Assert::equal($expected, $configManager->read($fileName));
	}

	/**
	 * @test
	 * Test function to save main configuration
	 */
	public function testSaveMain() {
		$path = __DIR__ . '/../configuration-test/';
		$configParser = new ConfigParser();
		$configManager = new ConfigManager($path, $configParser);
		$array = [
			"Configuration" => "v0.0",
			"ConfigurationDir" => "/etc/iqrf-daemon",
			"WatchDogTimeoutMilis" => 10000
		];
		$expected = Json::decode(FileSystem::read(__DIR__ . '/../configuration/config.json'), Json::FORCE_ARRAY);
		$configManager->write('config', $expected);
		$expected['ConfigurationDir'] = '/etc/iqrf-daemon';
		$configManager->saveMain(ArrayHash::from($array));
		Assert::equal($expected, $configManager->read('config'));
	}

	/**
	 * @test
	 * Test function to parse configuration of Scheduler
	 */
	public function testSaveScheduler() {
		$path = __DIR__ . '/../configuration-test/';
		$fileName = 'Scheduler';
		$configParser = new ConfigParser();
		$configManager = new ConfigManager($path, $configParser);
		$array = [
			"time" => "*/5 * * * * * *",
			"service" => "BaseServiceForMQTT1",
			"ctype" => "dpa",
			"type" => "std-sen",
			"nadr" => "0",
			"cmd" => "READ",
			"hwpid" => "ffff",
			"sensors" => "Temperature1" . PHP_EOL . "CO2_1" . PHP_EOL . "Humidity1"
		];
		$expected = Json::decode(FileSystem::read(__DIR__ . '/../configuration/Scheduler.json'), Json::FORCE_ARRAY);
		$configManager->write($fileName, $expected);
		$expected['TasksJson'][0]['message']['nadr'] = '0';
		$configManager->saveScheduler(ArrayHash::from($array), 0);
		Assert::equal($expected, $configManager->read($fileName));
	}

}

$test = new ConfigManagerTest($container);
$test->run();
