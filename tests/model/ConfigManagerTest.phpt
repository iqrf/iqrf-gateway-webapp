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
	 * Test function to parse configuration of Instances
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

}

$test = new ConfigManagerTest($container);
$test->run();
