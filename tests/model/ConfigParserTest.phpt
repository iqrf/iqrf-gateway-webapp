<?php

/**
 * TEST: App\Model\ConfigParser
 * @phpVersion >= 5.6
 * @testCase
 */
use App\Model\ConfigParser;
use Nette\DI\Container;
use Nette\Utils\ArrayHash;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../bootstrap.php';

class ConfigParserTest extends TestCase {

	private $container;

	function __construct(Container $container) {
		$this->container = $container;
	}

	/**
	 * @test
	 * Test function to parse configuration of Instances
	 */
	public function testInstancesToJson() {
		$path = __DIR__ . '/../configuration/';
		$configParser = new ConfigParser($path);
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
		Assert::equal($result, $configParser->instancesToJson($instances, $update, 1));
	}

	/**
	 * @test
	 * Test function to parse configuration of Instances
	 */
	public function testInstancesToForm() {
		$path = __DIR__ . '/../configuration/';
		$configParser = new ConfigParser($path);
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
		$json = [
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
			]
		];
		Assert::equal($array, $configParser->instancesToForm($json, 1));
	}

	/**
	 * @test
	 * Test function to parse configuration of Components
	 */
	public function testComponentsToJson() {
		$path = __DIR__ . '/../configuration/';
		$configParser = new ConfigParser($path);
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
		Assert::equal($result, $configParser->componentsToJson($array));
	}

}

$test = new ConfigParserTest($container);
$test->run();
