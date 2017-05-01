<?php

/**
 * TEST: App\Model\ConfigManager
 * @phpVersion >= 5.6
 * @testCase
 */
use App\Model\ConfigManager;
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
		$configManager = new ConfigManager($path);
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
		$configManager = new ConfigManager($path);
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
	 * Test function to parse configuration of Instances
	 */
	public function testParseInstances() {
		$path = __DIR__ . '/../configuration/';
		$configManager = new ConfigManager($path);
		$instances = [
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
			],
			[
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
		];
		$updateArray = [
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
			],
			[
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
				]
			]
		];
		$update = ArrayHash::from($updateArray);
		Assert::equal($result, $configManager->parseInstances($instances, $update, 1));
	}

	/**
	 * @test
	 * Test function to parse configuration of Components
	 */
	public function testParseComponents() {
		$path = __DIR__ . '/../configuration/';
		$configManager = new ConfigManager($path);
		$array = [
			"TracerFile" => true,
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
		];
		Assert::equal($result, $configManager->parseComponents($array));
	}

	/**
	 * @test
	 * Test function to save configuration of Components
	 */
	public function testSaveComponents() {
		$path = __DIR__ . '/../configuration-test/';
		$configManager = new ConfigManager($path);
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
		$configManager = new ConfigManager($path);
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

}

$test = new ConfigManagerTest($container);
$test->run();
