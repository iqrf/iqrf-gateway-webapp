<?php

/**
 * TEST: App\ConfigModule\Models\ComponentManager
 * @covers App\ConfigModule\Models\ComponentManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\ConfigModule\Models;

use App\ConfigModule\Models\ComponentManager;
use App\CoreModule\Models\JsonFileManager;
use Nette\Utils\Arrays;
use Tester\Assert;
use Tester\Environment;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for component configuration manager
 */
class ComponentManagerTest extends TestCase {

	/**
	 * @var JsonFileManager JSON file manager
	 */
	private $fileManager;

	/**
	 * @var JsonFileManager JSON file manager
	 */
	private $fileManagerTemp;

	/**
	 * @var string File name (without .json)
	 */
	private $fileName = 'config';

	/**
	 * @var ComponentManager Component configuration manager
	 */
	private $manager;

	/**
	 * @var ComponentManager Component configuration manager
	 */
	private $managerTemp;

	/**
	 * The function to add a new component
	 */
	public function testAdd(): void {
		Environment::lock('config_main', __DIR__ . '/../../temp/');
		$expected = $this->fileManager->read($this->fileName);
		$this->fileManagerTemp->write($this->fileName, $expected);
		$array = [
			'name' => 'test::Test',
			'libraryPath' => '',
			'libraryName' => 'Test',
			'enabled' => false,
			'startlevel' => 100,
		];
		$expected['components'][] = $array;
		$this->managerTemp->add($array);
		$actual = $this->fileManagerTemp->read($this->fileName)['components'];
		Assert::same($expected['components'], $actual);
	}

	/**
	 * Test function to delete the component
	 */
	public function testDelete(): void {
		Environment::lock('config_main', __DIR__ . '/../../temp/');
		$expected = $this->fileManager->read($this->fileName);
		$this->fileManagerTemp->write($this->fileName, $expected);
		unset($expected['components'][30]);
		$this->managerTemp->delete(30);
		$actual = $this->fileManagerTemp->read($this->fileName)['components'];
		Assert::same($expected['components'], $actual);
	}

	/**
	 * Test function to load configuration of components
	 */
	public function testList(): void {
		$components = $this->fileManager->read($this->fileName)['components'];
		$expected = [];
		foreach ($components as $id => $config) {
			$expected[$id] = Arrays::mergeTree(['id' => $id], $config);
		}
		Assert::equal($expected, $this->manager->list());
	}

	/**
	 * Test function to load configuration of components
	 */
	public function testLoad(): void {
		$json = $this->fileManager->read($this->fileName);
		$expected = $json['components'][0];
		Assert::equal($expected, $this->manager->load(0));
		Assert::equal([], $this->manager->load(100));
	}

	/**
	 * Test function to save configuration of components
	 */
	public function testSave(): void {
		Environment::lock('config_main', __DIR__ . '/../../temp/');
		$array = [
			'name' => 'iqrf::Scheduler',
			'libraryPath' => '',
			'libraryName' => 'Scheduler',
			'enabled' => false,
			'startlevel' => 1,
		];
		$expected = $this->fileManager->read($this->fileName);
		$this->fileManagerTemp->write($this->fileName, $expected);
		$expected['components'][6]['enabled'] = false;
		$this->managerTemp->save($array, 6);
		Assert::equal($expected, $this->fileManagerTemp->read($this->fileName));
	}

	/**
	 * Set up the test environment
	 */
	protected function setUp(): void {
		$configPath = __DIR__ . '/../../data/configuration/';
		$configTempPath = __DIR__ . '/../../temp/configuration/';
		$this->fileManager = new JsonFileManager($configPath);
		$this->fileManagerTemp = new JsonFileManager($configTempPath);
		$this->manager = new ComponentManager($this->fileManager);
		$this->managerTemp = new ComponentManager($this->fileManagerTemp);
	}

}

$test = new ComponentManagerTest();
$test->run();
