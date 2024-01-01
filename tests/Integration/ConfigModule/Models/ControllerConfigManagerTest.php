<?php

/**
 * TEST: App\ConfigModule\Models\ControllerConfigManager
 * @covers App\ConfigModule\Models\ControllerConfigManager
 * @phpVersion >= 7.4
 * @testCase
 */
/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
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

use App\ConfigModule\Models\ControllerConfigManager;
use App\CoreModule\Entities\CommandStack;
use App\CoreModule\Models\CommandManager;
use App\CoreModule\Models\FileManager;
use Nette\Utils\FileSystem;
use Tester\Assert;
use Tester\Environment;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for Controller configuration manager
 */
final class ControllerConfigManagerTest extends TestCase {

	/**
	 * Controller configuration directory path
	 */
	private const CONF_DIR = TESTER_DIR . '/data/controller/';

	/**
	 * Controller configuration temporary directory path
	 */
	private const TEMP_CONF_DIR = TMP_DIR . '/controller/';

	/**
	 * IQRF Gateway Controller's expected configuration
	 */
	private const CONFIG = [
		'daemonApi' => [
			'autoNetwork' => [
				'actionRetries' => 1,
				'discoveryBeforeStart' => true,
				'discoveryTxPower' => 6,
				'skipDiscoveryEachWave' => false,
				'stopConditions' => [
					'abortOnTooManyNodesFound' => false,
					'emptyWaves' => 2,
					'waves' => 2,
				],
				'returnVerbose' => false,
			],
			'discovery' => [
				'maxAddr' => 0,
				'txPower' => 6,
				'returnVerbose' => false,
			],
		],
		'factoryReset' => [
			'coordinator' => false,
			'daemon' => true,
			'network' => false,
			'webapp' => true,
		],
		'logger' => [
			'filePath' => '/var/log/iqrf-gateway-controller.log',
			'severity' => 'info',
		],
		'resetButton' => [
			'api' => '',
			'button' => 2,
		],
		'statusLed' => [
			'greenLed' => 0,
			'redLed' => 1,
		],
		'wsServers' => [
			'api' => 'ws://localhost:1338',
			'monitor' => 'ws://localhost:1438',
		],
	];

	/**
	 * @var ControllerConfigManager Controller configuration manager
	 */
	private ControllerConfigManager $manager;

	/**
	 * @var ControllerConfigManager Controller configuration temporary manager
	 */
	private ControllerConfigManager $managerTemp;

	/**
	 * Tests the function to retrieve Controller configuration
	 */
	public function testGetConfig(): void {
		Assert::equal(self::CONFIG, $this->manager->getConfig());
	}

	/**
	 * Tests the function to update Controller configuration file
	 */
	public function testSaveConfig(): void {
		Environment::lock('controller_config', TMP_DIR);
		FileSystem::copy(self::CONF_DIR, self::TEMP_CONF_DIR);
		$expected = self::CONFIG;
		$expected['daemonApi']['discovery']['maxAddr'] = 5;
		$expected['factoryReset']['coordinator'] = true;
		$expected['logger']['severity'] = 'debug';
		$expected['resetButton']['api'] = 'discovery';
		$expected['statusLed']['greenLed'] = 1;
		$expected['wsServers']['api'] = 'ws://localhost:1883';
		$this->managerTemp->saveConfig($expected);
		Assert::same($expected, $this->managerTemp->getConfig());
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$commandStack = new CommandStack();
		$commandManager = new CommandManager(false, $commandStack);
		$fileManager = new FileManager(self::CONF_DIR, $commandManager);
		$fileManagerTemp = new FileManager(self::TEMP_CONF_DIR, $commandManager);
		$this->manager = new ControllerConfigManager($fileManager);
		$this->managerTemp = new ControllerConfigManager($fileManagerTemp);
	}

}

$test = new ControllerConfigManagerTest();
$test->run();
