<?php

/**
 * TEST: App\ConfigModule\Model\InstanceManager
 * @covers App\ConfigModule\Model\InstanceManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types=1);

namespace Test\ConfigModule\Model;

use App\ConfigModule\Model\BaseServiceManager;
use App\ConfigModule\Model\InstanceManager;
use App\Model\JsonFileManager;
use Nette\DI\Container;
use Nette\Utils\ArrayHash;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

class InstanceManagerTest extends TestCase {

	/**
	 * @var Container Nette Tester Container
	 */
	private $container;

	/**
	 * @var JsonFileManager JSON file manager
	 */
	private $fileManager;

	/**
	 * @var JsonFileManager JSON file manager
	 */
	private $fileManagerTemp;

	/**
	 * @var BaseServiceManager Base service manager
	 */
	private $baseServiceManager;

	/**
	 * @var array
	 */
	private $array = [
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
		'EnabledSSL' => false,
		'KeepAliveInterval' => 20,
		'ConnectTimeout' => 5,
		'MinReconnect' => 1,
		'MaxReconnect' => 64,
		'TrustStore' => 'server-ca.crt',
		'KeyStore' => 'client.pem',
		'PrivateKey' => 'client-privatekey.pem',
		'PrivateKeyPassword' => '',
		'EnabledCipherSuites' => '',
		'EnableServerCertAuth' => true
	];

	/**
	 * @var string File name (without .json)
	 */
	private $fileName = 'MqttMessaging';

	/**
	 * @var string Directory with configuration files
	 */
	private $path = __DIR__ . '/../../configuration/';

	/**
	 * @var string Testing directory with configuration files
	 */
	private $pathTest = __DIR__ . '/../../configuration-test/';

	/**
	 * @var array Names of instances
	 */
	private $instancesNames = [
		'MqttMessaging1', 'MqttMessaging2',
	];

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
		$this->fileManager = new JsonFileManager($this->path);
		$this->fileManagerTemp = new JsonFileManager($this->pathTest);
		$this->baseServiceManager = new BaseServiceManager($this->fileManagerTemp);
	}

	/**
	 * @test
	 * Test function to delete configuration of Instances
	 */
	public function testDelete() {
		$manager = new InstanceManager($this->fileManagerTemp, $this->baseServiceManager);
		$manager->setFileName($this->fileName);
		$expected = $this->fileManager->read($this->fileName);
		$expectedBaseServices = $this->baseServiceManager->getServices();
		$this->fileManagerTemp->write($this->fileName, $expected);
		unset($expected['Instances'][1], $expectedBaseServices[2]);
		$manager->delete(1);
		Assert::equal($expected, $this->fileManagerTemp->read($this->fileName));
		Assert::equal($expectedBaseServices, $this->baseServiceManager->getServices());
	}

	/**
	 * @test
	 * Test function to delete configuration of Instances
	 */
	public function testDeleteByName() {
		$manager = new InstanceManager($this->fileManagerTemp, $this->baseServiceManager);
		$manager->setFileName($this->fileName);
		$expected = $this->fileManager->read($this->fileName);
		$expectedBaseServices = $this->baseServiceManager->getServices();
		$this->fileManagerTemp->write($this->fileName, $expected);
		unset($expected['Instances'][1], $expectedBaseServices[2]);
		$manager->deleteByName('MqttMessaging2');
		Assert::equal($expected, $this->fileManagerTemp->read($this->fileName));
		Assert::equal($expectedBaseServices, $this->baseServiceManager->getServices());
	}

	/**
	 * @test
	 * Test function to get list of instancesr
	 */
	public function testGetInstances() {
		$manager = new InstanceManager($this->fileManager, $this->baseServiceManager);
		$manager->setFileName($this->fileName);
		$expected = $this->fileManager->read($this->fileName)['Instances'];
		Assert::equal($expected, $manager->getInstances());
	}

	/**
	 * @test
	 * Test function to get list of names of instances
	 */
	public function testGetInstancesNames() {
		$manager = new InstanceManager($this->fileManager, $this->baseServiceManager);
		$manager->setFileName($this->fileName);
		Assert::equal($this->instancesNames, $manager->getInstancesNames());
	}

	/**
	 * @test
	 * Test function to parse configuration of Instances
	 */
	public function testLoad() {
		$manager = new InstanceManager($this->fileManager, $this->baseServiceManager);
		$manager->setFileName($this->fileName);
		Assert::equal($this->array, $manager->load(1));
		Assert::equal([], $manager->load(10));
	}

	/**
	 * @test
	 * Test function to save configuration of Instances
	 */
	public function testSave() {
		$manager = new InstanceManager($this->fileManagerTemp, $this->baseServiceManager);
		$manager->setFileName($this->fileName);
		$array = $this->array;
		$array['EnabledSSL'] = true;
		$expected = $this->fileManager->read($this->fileName);
		$this->fileManagerTemp->write($this->fileName, $expected);
		$expected['Instances'][1]['Properties']['EnabledSSL'] = true;
		$manager->save(ArrayHash::from($array), 1);
		Assert::equal($expected, $this->fileManagerTemp->read($this->fileName));
	}

	/**
	 * @test
	 * Test function to parse configuration of Instances
	 */
	public function testSaveJson() {
		$manager = new InstanceManager($this->fileManager, $this->baseServiceManager);
		$manager->setFileName($this->fileName);
		$instances = $this->fileManager->read($this->fileName)['Instances'];
		$array = $this->array;
		$array['EnabledSSL'] = true;
		$expected = $instances;
		$expected[1]['Properties']['EnabledSSL'] = true;
		Assert::equal($expected, $manager->saveJson($instances, ArrayHash::from($array), 1));
	}

}

$test = new InstanceManagerTest($container);
$test->run();
