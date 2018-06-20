<?php

/**
 * TEST: App\ConfigModule\Model\ComponentManager
 * @covers App\ConfigModule\Model\ComponentManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types=1);

namespace Test\ConfigModule\Model;

use App\ConfigModule\Model\ComponentManager;
use App\Model\JsonFileManager;
use Nette\DI\Container;
use Nette\Utils\ArrayHash;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

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
	private $fileManagerTemp;

	/**
	 * @var string File name (without .json)
	 */
	private $fileName = 'config';

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
		$this->fileManagerTemp = new JsonFileManager($this->pathTest);
	}

	/**
	 * @test
	 * Test function to load configuration of Components
	 */
	public function testLoadComponents() {
		$manager = new ComponentManager($this->fileManager);
		$json = $this->fileManager->read($this->fileName);
		$expected = $json['components'];
		Assert::equal($expected, $manager->loadComponents());
	}

	/**
	 * @test
	 * Test function to load configuration of Components
	 */
	public function testLoadComponent() {
		$manager = new ComponentManager($this->fileManager);
		$json = $this->fileManager->read($this->fileName);
		$expected = $json['components'][0];
		Assert::equal($expected, $manager->loadComponent(0));
		Assert::equal([], $manager->loadComponent(100));
	}

	/**
	 * @test
	 * Test function to save configuration of Components
	 */
	public function testSave() {
		$manager = new ComponentManager($this->fileManagerTemp);
		$array = [
			'name' => 'iqrf::Scheduler',
			'libraryPath' => '',
			'libraryName' => 'Scheduler',
			'enabled' => false,
			'startlevel' => 1,
		];
		$expected = $this->fileManager->read($this->fileName);
		$this->fileManagerTemp->write($this->fileName, $expected);
		$expected['components'][7]['enabled'] = false;
		$manager->save(ArrayHash::from($array),7);
		Assert::equal($expected, $this->fileManagerTemp->read($this->fileName));
	}

}

$test = new ComponentManagerTest($container);
$test->run();
