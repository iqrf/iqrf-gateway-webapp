<?php

/**
 * TEST: App\ConfigModule\Models\GenericManager
 * @covers App\ConfigModule\Models\GenericManager
 * @phpVersion >= 7.1
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Integration\ConfigModule\Models;

use App\ConfigModule\Models\GenericManager;
use Nette\Utils\Arrays;
use Tester\Assert;
use Tester\Environment;
use Tests\Toolkit\TestCases\JsonConfigTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for generic configuration manager
 */
class GenericManagerTest extends JsonConfigTestCase {

	/**
	 * @var string Component name
	 */
	private $component = 'iqrf::MqttMessaging';

	/**
	 * @var string File name (without .json)
	 */
	private $fileName = 'iqrf__MqttMessaging';

	/**
	 * @var GenericManager Generic configuration manager
	 */
	private $manager;

	/**
	 * @var GenericManager Generic configuration manager
	 */
	private $managerTemp;

	/**
	 * Tests the function to delete the instance of component
	 */
	public function testDelete(): void {
		Environment::lock('config_mqtt', __DIR__ . '/../../../temp/');
		$this->managerTemp->setComponent($this->component);
		$this->copyFile($this->fileName);
		Assert::true($this->fileManagerTemp->exists($this->fileName));
		$id = array_search($this->fileName, $this->managerTemp->getInstanceFiles(), true);
		$this->managerTemp->delete($id);
		Assert::false($this->fileManagerTemp->exists($this->fileName));
	}

	/**
	 * Tests the function to fix a required instance in the configuration
	 */
	public function testFixRequiredInterfaces(): void {
		$expected = $this->readFile('iqrf__WebsocketMessaging');
		$configuration = $expected;
		$expected['RequiredInterfaces'][0]['target']['instance'] = 'WebsocketCppService';
		unset($expected['RequiredInterfaces'][0]['target']['WebsocketPort']);
		$this->manager->fixRequiredInterfaces($configuration);
		Assert::same($expected, $configuration);
	}

	/**
	 * Tests the function to generate a file name
	 */
	public function testGenerateFileName(): void {
		$this->manager->setComponent($this->component);
		$array = $this->readFile($this->fileName);
		$this->manager->generateFileName($array);
		Assert::equal($this->fileName, $this->manager->getFileName());
	}

	/**
	 * Tests the function to get instance by it's property
	 */
	public function testGetInstanceByProperty(): void {
		Assert::same($this->fileName, $this->manager->getInstanceByProperty('instance', 'MqttMessaging'));
		Assert::same($this->fileName, $this->manager->getInstanceByProperty('BrokerAddr', 'tcp://127.0.0.1:1883'));
		Assert::null($this->manager->getInstanceByProperty('foo', 'bar'));
	}

	/**
	 * Tests the function to get component's instances
	 */
	public function testGetInstanceFiles(): void {
		$this->manager->setComponent($this->component);
		$expected = ['iqrf__MqttMessaging'];
		Assert::equal($expected, $this->manager->getInstanceFiles());
	}

	/**
	 * Tests the function to get available messagings
	 */
	public function testGetMessagings(): void {
		$expected = [
			'config.mq.title' => ['MqMessaging'],
			'config.mqtt.title' => ['MqttMessaging'],
			'config.udp.title' => ['UdpMessaging'],
			'config.websocket.title' => ['WebsocketMessaging'],
		];
		Assert::same($expected, $this->manager->getMessagings());
	}

	/**
	 * Tests the function to load the existing configuration
	 */
	public function testLoadExisting(): void {
		$this->manager->setComponent($this->component);
		$expected = $this->readFile($this->fileName);
		Assert::equal($expected, $this->manager->load(0));
	}

	/**
	 * Tests the function to load a nonexistent configuration
	 */
	public function testLoadNonexisting(): void {
		$this->manager->setComponent($this->component);
		Assert::same([], $this->manager->load(-1));
	}

	/**
	 * Tests the function to list configurations
	 */
	public function testList(): void {
		$this->manager->setComponent($this->component);
		$expected = [Arrays::mergeTree(['id' => 0], $this->readFile($this->fileName))];
		Assert::equal($expected, $this->manager->list());
	}

	/**
	 * Tests the function to save main configuration of daemon
	 */
	public function testSave(): void {
		Environment::lock('config_mqtt', __DIR__ . '/../../../temp/');
		$this->managerTemp->setComponent($this->component);
		$array = [
			'instance' => 'MqttMessaging',
			'BrokerAddr' => 'tcp://127.0.0.1:1883',
			'ClientId' => 'IqrfDpaMessaging',
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
			'EnableServerCertAuth' => true,
			'acceptAsyncMsg' => true,
		];
		$expected = $this->readFile($this->fileName);
		$this->copyFile($this->fileName);
		$expected['acceptAsyncMsg'] = true;
		$this->managerTemp->save($array);
		Assert::equal($expected, $this->fileManagerTemp->read($this->fileName));
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->manager = new GenericManager($this->fileManager, $this->schemaManager);
		$this->managerTemp = new GenericManager($this->fileManagerTemp, $this->schemaManager);
	}

}

$test = new GenericManagerTest();
$test->run();
