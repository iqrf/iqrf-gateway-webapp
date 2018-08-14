<?php

/**
 * TEST: App\ConfigModule\Model\ComponentManager
 * @covers App\ConfigModule\Model\ComponentManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\ConfigModule\Model;

use App\ConfigModule\Model\ComponentManager;
use App\Model\JsonFileManager;
use Nette\DI\Container;
use Nette\Utils\ArrayHash;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for component configuration manager
 */
class ComponentManagerTest extends TestCase {

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
	private $fileManagerTest;

	/**
	 * @var string File name (without .json)
	 */
	private $fileName = 'config';

	/**
	 * @var ComponentManager Componenct configuration manager
	 */
	private $manager;

	/**
	 * @var ComponentManager Component configuration manager
	 */
	private $managerTest;

	/**
	 * @var string Directory with configuration files
	 */
	private $path = __DIR__ . '/../../configuration/';

	/**
	 * @var string Testing directory with configuration files
	 */
	private $pathTest = __DIR__ . '/../../configuration-test/';

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
		$this->fileManagerTest = new JsonFileManager($this->pathTest);
		$this->manager = new ComponentManager($this->fileManager);
		$this->managerTest = new ComponentManager($this->fileManagerTest);
	}

	/**
	 * The function to add a new component
	 */
	public function testAdd() {
		$expected = $this->fileManager->read($this->fileName);
		$this->fileManagerTest->write($this->fileName, $expected);
		$array = [
			'name' => 'test::Test',
			'libraryPath' => '',
			'libraryName' => 'Test',
			'enabled' => false,
			'startlevel' => 100,
		];
		$expected['components'][] = $array;
		$this->managerTest->add(ArrayHash::from($array));
		Assert::same($expected['components'], $this->managerTest->loadComponents());
	}

	/**
	 * Test function to delete the component
	 */
	public function testDelete() {
		$expected = $this->fileManager->read($this->fileName);
		$this->fileManagerTest->write($this->fileName, $expected);
		unset($expected['components'][30]);
		$this->managerTest->delete(30);
		Assert::same($expected['components'], $this->managerTest->loadComponents());
	}

	/**
	 * Test function to load configuration of components
	 */
	public function testLoadComponents() {
		$json = $this->fileManager->read($this->fileName);
		$expected = $json['components'];
		Assert::equal($expected, $this->manager->loadComponents());
	}

	/**
	 * Test function to load configuration of components
	 */
	public function testLoadComponent() {
		$json = $this->fileManager->read($this->fileName);
		$expected = $json['components'][0];
		Assert::equal($expected, $this->manager->loadComponent(0));
		Assert::equal([], $this->manager->loadComponent(100));
	}

	/**
	 * Test function to save configuration of components
	 */
	public function testSave() {
		$array = [
			'name' => 'iqrf::Scheduler',
			'libraryPath' => '',
			'libraryName' => 'Scheduler',
			'enabled' => false,
			'startlevel' => 1,
		];
		$expected = $this->fileManager->read($this->fileName);
		$this->fileManagerTest->write($this->fileName, $expected);
		$expected['components'][6]['enabled'] = false;
		$this->managerTest->save(ArrayHash::from($array), 6);
		Assert::equal($expected, $this->fileManagerTest->read($this->fileName));
	}

}

$test = new ComponentManagerTest($container);
$test->run();
