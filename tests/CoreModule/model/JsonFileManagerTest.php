<?php

/**
 * TEST: App\CoreModule\Model\JsonFileManager
 * @covers App\CoreModule\Model\JsonFileManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\CoreModule\Model;

use App\CoreModule\Model\JsonFileManager;
use Nette\DI\Container;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for JSON file manager
 */
class JsonFileManagerTest extends TestCase {

	/**
	 * @var Container Nette Tester Container
	 */
	private $container;

	/**
	 * @var string File name
	 */
	private $fileName = 'config';

	/**
	 * @var JsonFileManager JSON File manager
	 */
	private $manager;

	/**
	 * @var JsonFileManager JSON File manager
	 */
	private $managerTest;

	/**
	 * @var string Directory with configuration files
	 */
	private $path = __DIR__ . '/../../data/configuration/';

	/**
	 * @var string Directory with configuration files
	 */
	private $pathTest = __DIR__ . '/../../temp/configuration/';

	/**
	 * Constructor
	 * @param Container $container Nette Tester Container
	 */
	public function __construct(Container $container) {
		$this->container = $container;
	}

	/**
	 * Set up the test environment
	 */
	protected function setUp(): void {
		$this->manager = new JsonFileManager($this->path);
		$this->managerTest = new JsonFileManager($this->pathTest);
	}

	/**
	 * Test function to get directory with files
	 */
	public function testGetDirectory(): void {
		Assert::same($this->path, $this->manager->getDirectory());
	}

	/**
	 * Test function to delete JSON file
	 */
	public function testDelete(): void {
		$fileName = 'test-delete';
		$this->managerTest->write($fileName, $this->manager->read($this->fileName));
		Assert::true($this->managerTest->exists($fileName));
		$this->managerTest->delete($fileName);
		Assert::false($this->managerTest->exists($fileName));
	}

	/**
	 * Test function to check if JSON file exists (file is not exist)
	 */
	public function testExistsFail(): void {
		Assert::false($this->manager->exists('nonsense'));
	}

	/**
	 * Test function to check if JSON file exists (file is exist)
	 */
	public function testExistsSucecss(): void {
		Assert::true($this->manager->exists($this->fileName));
	}

	/**
	 * Test function to read JSON file
	 */
	public function testRead(): void {
		$text = FileSystem::read($this->path . $this->fileName . '.json');
		$expected = Json::decode($text, Json::FORCE_ARRAY);
		Assert::equal($expected, $this->manager->read($this->fileName));
	}

	/**
	 * Test function to write JSON file
	 */
	public function testWrite(): void {
		$fileName = 'config-test';
		$expected = $this->manager->read($this->fileName);
		$this->managerTest->write($fileName, $expected);
		Assert::equal($expected, $this->managerTest->read($fileName));
	}

}

$test = new JsonFileManagerTest($container);
$test->run();
