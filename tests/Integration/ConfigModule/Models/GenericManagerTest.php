<?php

/**
 * TEST: App\ConfigModule\Models\GenericManager
 * @covers App\ConfigModule\Models\GenericManager
 * @phpVersion >= 7.4
 * @testCase
 */
/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
declare(strict_types = 1);

namespace Tests\Integration\ConfigModule\Models;

use App\ConfigModule\Models\GenericManager;
use Tester\Assert;
use Tester\Environment;
use Tests\Toolkit\TestCases\JsonConfigTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for generic configuration manager
 */
final class GenericManagerTest extends JsonConfigTestCase {

	/**
	 * Component name
	 */
	private const COMPONENT = 'iqrf::MqttMessaging';

	/**
	 * File name
	 */
	private const FILE_NAME = 'iqrf__MqttMessaging';

	/**
	 * @var GenericManager Generic configuration manager
	 */
	private GenericManager $manager;

	/**
	 * @var GenericManager Generic configuration manager
	 */
	private GenericManager $managerTemp;

	/**
	 * Tests the function to delete the instance of component
	 */
	public function testDeleteFile(): void {
		Environment::lock('config_mqtt', TMP_DIR);
		$this->managerTemp->setComponent(self::COMPONENT);
		$this->copyFile(self::FILE_NAME . '.json');
		Assert::true($this->fileManagerTemp->exists(self::FILE_NAME . '.json'));
		$this->managerTemp->deleteFile(self::FILE_NAME);
		Assert::false($this->fileManagerTemp->exists(self::FILE_NAME . '.json'));
	}

	/**
	 * Tests the function to delete the instance of component (missing file name)
	 */
	public function testDeleteFileInvalid(): void {
		$this->manager->setComponent(self::COMPONENT);
		Assert::noError(function (): void {
			$this->manager->deleteFile(null);
		});
	}

	/**
	 * Tests the function to fix a required instance in the configuration
	 */
	public function testFixRequiredInterfaces(): void {
		$expected = $this->readFile('iqrf__WebsocketMessaging.json');
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
		$this->manager->setComponent(self::COMPONENT);
		$array = $this->readFile(self::FILE_NAME . '.json');
		Assert::equal('iqrf__MqttMessaging__MqttMessaging', $this->manager->generateFileName($array));
	}

	/**
	 * Tests the function to get instance by it's property
	 */
	public function testGetInstanceByProperty(): void {
		Assert::same(self::FILE_NAME, $this->manager->getInstanceByProperty('instance', 'MqttMessaging'));
		Assert::same(self::FILE_NAME, $this->manager->getInstanceByProperty('BrokerAddr', 'tcp://127.0.0.1:1883'));
		Assert::null($this->manager->getInstanceByProperty('foo', 'bar'));
	}

	/**
	 * Tests the function to get instance configuration file name (failure)
	 */
	public function testGetInstanceFileNameFailure(): void {
		$this->manager->setComponent(self::COMPONENT);
		$instanceName = 'nonsense';
		Assert::null($this->manager->getInstanceFileName($instanceName));
	}

	/**
	 * Tests the function to get instance configuration file name (success)
	 */
	public function testGetInstanceFileNameSuccess(): void {
		$this->manager->setComponent(self::COMPONENT);
		$instanceName = 'MqttMessaging';
		Assert::same(self::FILE_NAME, $this->manager->getInstanceFileName($instanceName));
	}

	/**
	 * Tests the function to get component's instances
	 */
	public function testGetInstanceFiles(): void {
		$this->manager->setComponent(self::COMPONENT);
		$expected = ['iqrf__MqttMessaging'];
		Assert::equal($expected, $this->manager->getInstanceFiles());
	}

	/**
	 * Tests the function to load the existing configuration
	 */
	public function testLoadInstanceExisting(): void {
		$this->manager->setComponent(self::COMPONENT);
		$expected = $this->readFile(self::FILE_NAME . '.json');
		Assert::equal($expected, $this->manager->loadInstance('MqttMessaging'));
	}

	/**
	 * Tests the function to load a nonexistent configuration
	 */
	public function testLoadInstanceNonexisting(): void {
		$this->manager->setComponent(self::COMPONENT);
		Assert::same([], $this->manager->loadInstance('nonsense'));
	}

	/**
	 * Tests the function to list configurations
	 */
	public function testList(): void {
		$this->manager->setComponent(self::COMPONENT);
		$expected = [$this->readFile(self::FILE_NAME . '.json')];
		Assert::equal($expected, $this->manager->list());
	}

	/**
	 * Tests the function to save main configuration of daemon
	 */
	public function testSave(): void {
		Environment::lock('config_mqtt', TMP_DIR);
		$this->managerTemp->setComponent(self::COMPONENT);
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
		$expected = $this->readFile(self::FILE_NAME . '.json');
		$this->copyFile(self::FILE_NAME . '.json');
		$expected['acceptAsyncMsg'] = true;
		$this->managerTemp->save($array, self::FILE_NAME);
		Assert::equal($expected, $this->fileManagerTemp->readJson(self::FILE_NAME . '.json'));
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
