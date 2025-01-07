<?php

/**
 * TEST: App\ConfigModule\Models\ComponentManager
 * @covers App\ConfigModule\Models\ComponentManager
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

use App\ConfigModule\Models\ComponentManager;
use Nette\Utils\Arrays;
use Tester\Assert;
use Tester\Environment;
use Tests\Toolkit\TestCases\JsonConfigTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for component configuration manager
 */
final class ComponentManagerTest extends JsonConfigTestCase {

	/**
	 * File name
	 */
	private const FILE_NAME = 'config.json';

	/**
	 * @var ComponentManager Component configuration manager
	 */
	private ComponentManager $manager;

	/**
	 * @var ComponentManager Component configuration manager
	 */
	private ComponentManager $managerTemp;

	/**
	 * Tests the function to add a new component
	 */
	public function testAdd(): void {
		Environment::lock('config_main', TMP_DIR);
		$expected = $this->readFile(self::FILE_NAME);
		$this->copyFile(self::FILE_NAME);
		$array = [
			'name' => 'test::Test',
			'libraryPath' => '',
			'libraryName' => 'Test',
			'enabled' => false,
			'startlevel' => 100,
		];
		$expected['components'][] = $array;
		$this->managerTemp->add($array);
		Assert::same($expected, $this->readTempFile(self::FILE_NAME));
	}

	/**
	 * Tests the function to delete the component
	 */
	public function testDelete(): void {
		Environment::lock('config_main', TMP_DIR);
		$expected = $this->readFile(self::FILE_NAME);
		$this->copyFile(self::FILE_NAME);
		unset($expected['components'][30]);
		$expected['components'] = array_values($expected['components']);
		$this->managerTemp->delete(30);
		Assert::same($expected, $this->readTempFile(self::FILE_NAME));
	}

	/**
	 * Tests the function to get component ID
	 */
	public function testGetId(): void {
		Assert::same(8, $this->manager->getId('iqrf::IqrfSpi'));
		Assert::null($this->manager->getId('nonsense'));
	}

	/**
	 * Tests the function to load configuration of components
	 */
	public function testList(): void {
		/** @var array<array<string,bool|int|string>> $components */
		$components = $this->readFile(self::FILE_NAME)['components'];
		$expected = [];
		foreach ($components as $id => $config) {
			$expected[$id] = Arrays::mergeTree(['id' => $id], $config);
		}
		Assert::equal($expected, $this->manager->list());
	}

	/**
	 * Tests the function to load configuration of components
	 */
	public function testLoad(): void {
		$json = $this->readFile(self::FILE_NAME);
		$expected = $json['components'][0];
		Assert::equal($expected, $this->manager->load(0));
		Assert::equal([], $this->manager->load(100));
	}

	/**
	 * Tests the function to save configuration of components
	 */
	public function testSave(): void {
		Environment::lock('config_main', TMP_DIR);
		$array = [
			'name' => 'iqrf::Scheduler',
			'libraryPath' => '',
			'libraryName' => 'Scheduler',
			'enabled' => false,
			'startlevel' => 1,
		];
		$expected = $this->readFile(self::FILE_NAME);
		$this->copyFile(self::FILE_NAME);
		$expected['components'][6]['enabled'] = false;
		$this->managerTemp->save($array, 6);
		Assert::equal($expected, $this->fileManagerTemp->readJson(self::FILE_NAME));
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->manager = new ComponentManager($this->fileManager);
		$this->managerTemp = new ComponentManager($this->fileManagerTemp);
	}

}

$test = new ComponentManagerTest();
$test->run();
