<?php

/**
 * TEST: App\Model\ConfigParser
 * @phpVersion >= 5.6
 * @testCase
 */

namespace Test\Model;

use App\Model\ConfigParser;
use Nette\DI\Container;
use Nette\Utils\ArrayHash;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;
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
	 * Test function to parse configuration of Scheduler
	 */
	public function testBaseServiceToForm() {
		$path = __DIR__ . '/../configuration/';
		$configParser = new ConfigParser($path);
		$expected = [
			"Name" => "BaseServiceForMQ",
			"Messaging" => "MqMessaging",
			"Serializers" => [
				"SimpleSerializer",
				"JsonSerializer"
			]
		];

		$json = Json::decode(FileSystem::read(__DIR__ . '/../configuration/BaseService.json'), Json::FORCE_ARRAY);
		Assert::equal($expected, $configParser->baseServiceToForm($json, 0));
	}

	/**
	 * @test
	 * Test function to parse configuration of Scheduler
	 */
	public function testBaseServiceToJson() {
		$path = __DIR__ . '/../configuration/';
		$configParser = new ConfigParser($path);
		$updateArray = [
			"Name" => "BaseServiceForMQ1",
			"Messaging" => "MqMessaging",
			"Serializers" => [
				"SimpleSerializer",
				"JsonSerializer"
			],
			"Properties" => [
			]
		];
		$json = Json::decode(FileSystem::read(__DIR__ . '/../configuration/BaseService.json'), Json::FORCE_ARRAY);
		$expected = $json;
		$expected['Instances'][0]['Name'] = 'BaseServiceForMQ1';
		$update = ArrayHash::from($updateArray);
		Assert::equal($expected, $configParser->baseServiceToJson($json, $update, 0));
	}

	/**
	 * @test
	 * Test function to parse configuration of Instances
	 */
	public function testInstancesToJson() {
		$path = __DIR__ . '/../configuration/';
		$configParser = new ConfigParser($path);
		$instances = Json::decode(FileSystem::read(__DIR__ . '/../configuration/MqttMessaging.json'), Json::FORCE_ARRAY)['Instances'];
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
		$expected = [
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
					"MaxReconnect" => 64,
					"TrustStore" => "server-ca.crt",
					"KeyStore" => "client.pem",
					"PrivateKey" => "client-privatekey.pem",
					"PrivateKeyPassword" => "",
					"EnabledCipherSuites" => "",
					"EnableServerCertAuth" => true
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
		Assert::equal($expected, $configParser->instancesToJson($instances, $update, 1));
	}

	/**
	 * @test
	 * Test function to parse configuration of Instances
	 */
	public function testInstancesToForm() {
		$path = __DIR__ . '/../configuration/';
		$configParser = new ConfigParser($path);
		$expected = [
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
			"EnabledSSL" => false,
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
		$json = Json::decode(FileSystem::read(__DIR__ . '/../configuration/MqttMessaging.json'), Json::FORCE_ARRAY);
		Assert::equal($expected, $configParser->instancesToForm($json, 1));
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
		$expected = Json::decode(FileSystem::read(__DIR__ . '/../configuration/config.json'), Json::FORCE_ARRAY)['Components'];
		Assert::equal($expected, $configParser->componentsToJson(ArrayHash::from($array)));
	}

	/**
	 * @test
	 * Test function to parse configuration of Scheduler
	 */
	public function testSchedulerToForm() {
		$path = __DIR__ . '/../configuration/';
		$configParser = new ConfigParser($path);
		$expected = [
			"time" => "*/5 * * * * * *",
			"service" => "BaseServiceForMQTT1",
			"ctype" => "dpa",
			"type" => "std-sen",
			"nadr" => "1",
			"cmd" => "READ",
			"hwpid" => "ffff",
			"sensors" => "Temperature1\nCO2_1\nHumidity1"
		];
		$json = Json::decode(FileSystem::read(__DIR__ . '/../configuration/Scheduler.json'), Json::FORCE_ARRAY);
		Assert::equal($expected, $configParser->schedulerToForm($json, 0));
	}

	/**
	 * @test
	 * Test function to parse configuration of Scheduler
	 */
	public function testSchedulerToJson() {
		$path = __DIR__ . '/../configuration/';
		$configParser = new ConfigParser($path);
		$updateArray = [
			"time" => "*/5 * * * * * *",
			"service" => "BaseServiceForMQTT1",
			"ctype" => "dpa",
			"type" => "std-sen",
			"nadr" => "0",
			"cmd" => "READ",
			"hwpid" => "ffff",
			"sensors" => "Temperature1\nCO2_1\nHumidity1"
		];
		$json = Json::decode(FileSystem::read(__DIR__ . '/../configuration/Scheduler.json'), Json::FORCE_ARRAY);
		$expected = $json;
		$expected['TasksJson'][0]['message']['nadr'] = '0';
		$update = ArrayHash::from($updateArray);
		Assert::equal($expected, $configParser->schedulerToJson($json, $update, 0));
	}

}

$test = new ConfigParserTest($container);
$test->run();
