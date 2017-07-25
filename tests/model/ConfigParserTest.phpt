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
		$array = [
			"Name" => "BaseServiceForMQ",
			"Messaging" => "MqMessaging",
			"Serializers" => [
				"SimpleSerializer",
				"JsonSerializer"
			]
		];

		$json = [
			"Implements" => "IService",
			"Instances" => [
				[
					"Name" => "BaseServiceForMQ",
					"Messaging" => "MqMessaging",
					"Serializers" => [
						"SimpleSerializer",
						"JsonSerializer"
					],
					"Properties" => [
					]
				], [
					"Name" => "BaseServiceForMQTT1",
					"Messaging" => "MqttMessaging1",
					"Serializers" => [
						"JsonSerializer"
					],
					"Properties" => [
					]
				], [
					"Name" => "BaseServiceForMQTT2",
					"Messaging" => "MqttMessaging2",
					"Serializers" => [
						"JsonSerializer"
					],
					"Properties" => [
					]
				]
			]
		];
		Assert::equal($array, $configParser->baseServiceToForm($json, 0));
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
		$json = [
			"Implements" => "IService",
			"Instances" => [
				[
					"Name" => "BaseServiceForMQ",
					"Messaging" => "MqMessaging",
					"Serializers" => [
						"SimpleSerializer",
						"JsonSerializer"
					],
					"Properties" => [
					]
				], [
					"Name" => "BaseServiceForMQTT1",
					"Messaging" => "MqttMessaging1",
					"Serializers" => [
						"JsonSerializer"
					],
					"Properties" => [
					]
				], [
					"Name" => "BaseServiceForMQTT2",
					"Messaging" => "MqttMessaging2",
					"Serializers" => [
						"JsonSerializer"
					],
					"Properties" => [
					]
				]
			]
		];
		$result = $json;
		$result['Instances'][0]['Name'] = 'BaseServiceForMQ1';
		$update = ArrayHash::from($updateArray);
		Assert::equal($result, $configParser->baseServiceToJson($json, $update, 0));
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

	/**
	 * @test
	 * Test function to parse configuration of Scheduler
	 */
	public function testSchedulerToForm() {
		$path = __DIR__ . '/../configuration/';
		$configParser = new ConfigParser($path);
		$array = [
			"time" => "*/5 * * * * * *",
			"service" => "BaseServiceForMQTT1",
			"ctype" => "dpa",
			"type" => "std-sen",
			"nadr" => "1",
			"cmd" => "READ",
			"hwpid" => "ffff",
			"sensors" => "Temperature1\nCO2_1\nHumidity1"
		];
		$json = [
			"Tasks" => [],
			"TasksJson" => [
				[
					"time" => "*/5 * * * * * *",
					"service" => "BaseServiceForMQTT1",
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
		Assert::equal($array, $configParser->schedulerToForm($json, 0));
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
		$json = [
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
		$result = $json;
		$result['TasksJson'][0]['message']['nadr'] = '0';
		$update = ArrayHash::from($updateArray);
		Assert::equal($result, $configParser->schedulerToJson($json, $update, 0));
	}

}

$test = new ConfigParserTest($container);
$test->run();
