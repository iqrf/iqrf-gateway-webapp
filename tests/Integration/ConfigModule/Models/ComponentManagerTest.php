<?php

/**
 * TEST: App\ConfigModule\Models\ComponentManager
 * @covers App\ConfigModule\Models\ComponentManager
 * @phpVersion >= 7.2
 * @testCase
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
	 * File name (without .json)
	 */
	private const FILE_NAME = 'config';

	/**
	 * @var ComponentManager Component configuration manager
	 */
	private $manager;

	/**
	 * @var ComponentManager Component configuration manager
	 */
	private $managerTemp;

	/**
	 * Tests the function to add a new component
	 */
	public function testAdd(): void {
		Environment::lock('config_main', __DIR__ . '/../../../temp/');
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
		Environment::lock('config_main', __DIR__ . '/../../../temp/');
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
		$components = $this->readFile(self::FILE_NAME)['components'];
		$expected = [];
		foreach ($components as $id => $config) {
			$expected[$id] = Arrays::mergeTree(['id' => $id], $config);
		}
		Assert::equal($expected, $this->manager->list());
	}

	/**
	 * Tests the function to list disabled components
	 */
	public function testListDisabled(): void {
		$expected = ['iqrf::IqrfCdc' => false, 'iqrf::IqrfUart' => false];
		Assert::equal($expected, $this->manager->listDisabled());
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
		Environment::lock('config_main', __DIR__ . '/../../../temp/');
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
		Assert::equal($expected, $this->fileManagerTemp->read(self::FILE_NAME));
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
