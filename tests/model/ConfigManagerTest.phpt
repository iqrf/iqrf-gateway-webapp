<?php

/**
 * TEST: App\Model\ConfigManager
 * @phpVersion >= 5.6
 * @testCase
 */
use App\Model\ConfigManager;
use App\Model\ConfigParser;
use Nette\DI\Container;
use Nette\Utils\ArrayHash;
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
		$result = [
			'Configuration' => 'v0.0',
			'ConfigurationDir' => 'configuration',
			'WatchDogTimeoutMilis' => 10000,
			'Components' => [
				[
					"ComponentName" => "TracerFile",
					"Enabled" => true
				], [
					"ComponentName" => "IqrfInterface",
					"Enabled" => true
				], [
					"ComponentName" => "UdpMessaging",
					"Enabled" => true
				], [
					"ComponentName" => "MqttMessaging",
					"Enabled" => true
				], [
					"ComponentName" => "MqMessaging",
					"Enabled" => true
				], [
					"ComponentName" => "Scheduler",
					"Enabled" => true
				], [
					"ComponentName" => "SimpleSerializer",
					"Enabled" => true
				], [
					"ComponentName" => "JsonSerializer",
					"Enabled" => true
				], [
					"ComponentName" => "BaseService",
					"Enabled" => true
				]
			]
		];
		Assert::equal($result, $configManager->read('config'));
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
		$result = [
			"Implements" => "IMessaging",
			"Instances" => [
				[
					"Name" => "MqMessaging",
					"Enabled" => true,
					"Properties" => [
						"LocalMqName" => "iqrf-daemon-110",
						"RemoteMqName" => "iqrf-daemon-100"
					]
				]
			]
		];
		$configManager->write($fileName, $result);
		Assert::equal($result, $configManager->read($fileName));
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
		$result = [
			"Configuration" => "v0.0",
			"ConfigurationDir" => "configuration",
			"WatchDogTimeoutMilis" => 10000,
			"Components" => [
				[
					"ComponentName" => "TracerFile",
					"Enabled" => true
				],
				[
					"ComponentName" => "IqrfInterface",
					"Enabled" => true
				],
				[
					"ComponentName" => "UdpMessaging",
					"Enabled" => true
				],
				[
					"ComponentName" => "MqttMessaging",
					"Enabled" => true
				],
				[
					"ComponentName" => "MqMessaging",
					"Enabled" => true
				],
				[
					"ComponentName" => "Scheduler",
					"Enabled" => true
				],
				[
					"ComponentName" => "SimpleSerializer",
					"Enabled" => true
				],
				[
					"ComponentName" => "JsonSerializer",
					"Enabled" => true
				],
				[
					"ComponentName" => "BaseService",
					"Enabled" => true
				]
			]
		];
		$configManager->write('config', $result);
		$result['Components'][0]['Enabled'] = false;
		$configManager->saveComponents($array);
		Assert::equal($result, $configManager->read('config'));
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
			"TrustStore" => "/etc/letsencrypt/live/mqtt.example.com/chain.pem",
			"KeyStore" => "/etc/letsencrypt/live/mqtt.example.com/cert.pem",
			"PrivateKey" => "/etc/letsencrypt/live/mqtt.example.com/privkey.pem",
			"PrivateKeyPassword" => "",
			"EnabledCipherSuites" => "",
			"EnableServerCertAuth" => true
		];
		$result = [
			"Implements" => "IMessaging",
			"Instances" => [
				[
					"Name" => "MqttMessaging1",
					"Enabled" => true,
					"Properties" => [
						"BrokerAddr" => "tcp://127.0.0.1:1883",
						"ClientId" => "IqrfDpaMessaging1",
						"Persistence" => 1,
						"Qos" => 1,
						"TopicRequest" => "Iqrf/DpaRequest",
						"TopicResponse" => "Iqrf/DpaResponse",
						"User" => "",
						"Password" => "",
						"EnabledSSL" => false,
						"KeepAliveInterval" => 20,
						"ConnectTimeout" => 5,
						"MinReconnect" => 1,
						"MaxReconnect" => 64
					]
				], [
					"Name" => "MqttMessaging2",
					"Enabled" => false,
					"Properties" => [
						"BrokerAddr" => "tcp://iot.eclipse.org:1883",
						"ClientId" => "IqrfDpaMessaging2",
						"Persistence" => 1,
						"Qos" => 1,
						"TopicRequest" => "Iqrf/DpaRequest",
						"TopicResponse" => "Iqrf/DpaResponse",
						"User" => "",
						"Password" => "",
						"EnabledSSL" => false,
						"KeepAliveInterval" => 20,
						"ConnectTimeout" => 5,
						"MinReconnect" => 1,
						"MaxReconnect" => 64,
						"TrustStore" => "/etc/letsencrypt/live/mqtt.example.com/chain.pem",
						"KeyStore" => "/etc/letsencrypt/live/mqtt.example.com/cert.pem",
						"PrivateKey" => "/etc/letsencrypt/live/mqtt.example.com/privkey.pem",
						"PrivateKeyPassword" => "",
						"EnabledCipherSuites" => "",
						"EnableServerCertAuth" => true
					]
				]
			]
		];
		$configManager->write($fileName, $result);
		$result['Instances'][1]['Properties']['EnabledSSL'] = true;
		$configManager->saveInstances($fileName, ArrayHash::from($array), 1);
		Assert::equal($result, $configManager->read($fileName));
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
			"sensors" => "Temperature1\nCO2_1\nHumidity1"
		];
		$result = [
			"Tasks" => [],
			"TasksJson" => [
				[
					"service" => "BaseServiceForMQTT1",
					"time" => "*/5 * * * * * *",
					"message" => [
						"ctype" => "dpa",
						"type" => "std-sen",
						"nadr" => "1",
						"cmd" => "READ",
						"hwpid" => "ffff",
						"sensors" => [
							"Temperature1",
							"CO2_1",
							"Humidity1"
						]
					]
				], [
					"time" => "*/5 1 * * * * *",
					"service" => "BaseServiceForMQTT1",
					"message" => [
						"ctype" => "dpa",
						"type" => "std-per-ledg",
						"nadr" => "1",
						"cmd" => "PULSE",
						"hwpid" => "ffff",
						"timeout" => 200,
						"msgid" => "",
						"request" => ".",
						"request_ts" => "",
						"response" => ".",
						"response_ts" => "",
						"confirmation" => ".",
						"confirmation_ts" => "",
						"rcode" => "",
						"dpaval" => ""
					]
				], [
					"time" => "*/5 1 * * * * *",
					"service" => "BaseServiceForMQTT1",
					"message" => [
						"ctype" => "dpa",
						"type" => "raw",
						"request" => "01.00.06.03.ff.ff"
					]
				], [
					"time" => "*/5 1 * * * * *",
					"service" => "BaseServiceForMQTT1",
					"message" => [
						"ctype" => "dpa",
						"type" => "raw-hdp",
						"nadr" => "1",
						"pnum" => "06",
						"pcmd" => "3"
					]
				], [
					"time" => "*/5 1 * * * * *",
					"service" => "BaseServiceForMQTT1",
					"message" => [
						"ctype" => "dpa",
						"type" => "raw-hdp",
						"msgid" => "1",
						"timeout" => 1000,
						"nadr" => "00",
						"pnum" => "0d",
						"pcmd" => "00",
						"hwpid" => "ffff",
						"req_data" => "c0.00.00",
						"request" => ".",
						"request_ts" => "",
						"confirmation" => ".",
						"confirmation_ts" => "",
						"response" => ".",
						"response_ts" => ""
					]
				], [
					"time" => "*/5 1 * * * * *",
					"service" => "BaseServiceForMQTT1",
					"message" => [
						"ctype" => "dpa",
						"type" => "std-per-frc",
						"msgid" => "1",
						"timeout" => 5000,
						"cmd" => "SEND",
						"hwpid" => "ffff",
						"frc_type" => "GET_BYTE",
						"frc_user" => 0,
						"user_data" => "00.00",
						"request" => ".",
						"request_ts" => "",
						"confirmation" => ".",
						"confirmation_ts" => "",
						"response" => ".",
						"response_ts" => ""
					]
				]
			]
		];
		$configManager->write($fileName, $result);
		$result['TasksJson'][0]['message']['nadr'] = '0';
		$configManager->saveScheduler(ArrayHash::from($array), 0);
		Assert::equal($result, $configManager->read($fileName));
	}

}

$test = new ConfigManagerTest($container);
$test->run();
