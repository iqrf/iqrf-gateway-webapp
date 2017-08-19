<?php

/**
 * TEST: App\ConfigModule\Model\InstanceManager
 * @phpVersion >= 5.6
 * @testCase
 */

namespace Test\ConfigModule\Model;

use App\ConfigModule\Model\InstanceManager;
use App\Model\JsonFileManager;
use Nette\DI\Container;
use Nette\Utils\ArrayHash;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

class InstanceManagerTest extends TestCase {

	/**
	 * @var Container
	 */
	private $container;

	/**
	 * @var string
	 */
	private $fileName = 'MqttMessaging';

	/**
	 * @var string
	 */
	private $path = __DIR__ . '/../../configuration/';

	/**
	 * @var string
	 */
	private $pathTest = __DIR__ . '/../../configuration-test/';

	/**
	 * Constructor
	 * @param Container $container
	 */
	public function __construct(Container $container) {
		$this->container = $container;
	}

	/**
	 * @test
	 * Test function to parse configuration of Scheduler
	 */
	public function testLoad() {
		$fileManager = new JsonFileManager($this->path);
		$manager = new InstanceManager($fileManager);
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
		Assert::equal($expected, $manager->load($this->fileName, 1));
		Assert::equal([], $manager->load($this->fileName, 10));
	}

	/**
	 * @test
	 * Test function to parse configuration of Instances
	 */
	public function testSave() {
		$fileManager = new JsonFileManager($this->pathTest);
		$manager = new InstanceManager($fileManager);
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
		$expected = Json::decode(FileSystem::read($this->path . $this->fileName . '.json'), Json::FORCE_ARRAY);
		$fileManager->write($this->fileName, $expected);
		$expected['Instances'][1]['Properties']['EnabledSSL'] = true;
		$manager->save($this->fileName, ArrayHash::from($array), 1);
		Assert::equal($expected, $fileManager->read($this->fileName));
	}

	/**
	 * @test
	 * Test function to parse configuration of Instances
	 */
	public function testSaveJson() {
		$fileManager = new JsonFileManager($this->path);
		$manager = new InstanceManager($fileManager);
		$instances = Json::decode(FileSystem::read($this->path . 'MqttMessaging.json'), Json::FORCE_ARRAY)['Instances'];
		$update = [
			'Name' => 'MqttMessaging2',
			'Enabled' => false,
			'BrokerAddr' => 'tcp://iot.eclipse.org:1883',
			'ClientId' => 'IqrfDpaMessaging2',
			'Persistence' => 1,
			'Qos' => 1,
			'TopicRequest' => 'Iqrf/DpaRequest',
			'TopicResponse' => 'Iqrf/DpaResponse',
			'User' => '',
			'Password' => '',
			'EnabledSSL' => true,
			'KeepAliveInterval' => 20,
			'ConnectTimeout' => 5,
			'MinReconnect' => 1,
			'MaxReconnect' => 64,
			'TrustStore' => 'server-ca.crt',
			'KeyStore' => 'client.pem',
			'PrivateKey' => 'client-privatekey.pem',
			'PrivateKeyPassword' => '',
			'EnabledCipherSuites' => '',
			'EnableServerCertAuth' => true,
		];
		$expected = $instances;
		$expected[1]['Properties']['EnabledSSL'] = true;
		Assert::equal($expected, $manager->saveJson($instances, ArrayHash::from($update), 1));
	}

}

$test = new InstanceManagerTest($container);
$test->run();
