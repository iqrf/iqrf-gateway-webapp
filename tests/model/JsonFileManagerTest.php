<?php

/**
 * TEST: App\Model\JsonFileManager
 * @covers App\Model\JsonFileManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\Model;

use App\Model\JsonFileManager;
use Nette\DI\Container;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../bootstrap.php';

/**
 * Tests for JSON file manager
 */
class JsonFileManagerTest extends TestCase {

	/**
	 * @var Container Nette Tester Container
	 */
	private $container;

	/**
	 * @var JsonFileManager JSON File manager
	 */
	private $fileManager;

	/**
	 * @var JsonFileManager JSON File manager
	 */
	private $fileManagerTest;

	/**
	 * @var string Directory with configuration files
	 */
	private $path = __DIR__ . '/../configuration/';

	/**
	 * @var string Directory with configuration files
	 */
	private $pathTest = __DIR__ . '/../configuration-test/';

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
	}

	/**
	 * Test function to delete JSON file
	 */
	public function testDelete() {
		$fileName = 'test-delete';
		$this->fileManagerTest->write($fileName, $this->fileManager->read('config'));
		Assert::true($this->fileManagerTest->exists($fileName));
		$this->fileManagerTest->delete($fileName);
		Assert::false($this->fileManagerTest->exists($fileName));
	}

	/**
	 * Test function to check if JSON file exists
	 */
	public function testExists() {
		Assert::true($this->fileManager->exists('config'));
		Assert::false($this->fileManager->exists('nonsense'));
	}

	/**
	 * Test function to read JSON file
	 */
	public function testRead() {
		$expected = Json::decode(FileSystem::read($this->path . 'config.json'), Json::FORCE_ARRAY);
		Assert::equal($expected, $this->fileManager->read('config'));
	}

	/**
	 * Test function to write JSON file
	 */
	public function testWrite() {
		$fileName = 'config-test';
		$expected = $this->fileManager->read('config');
		$this->fileManagerTest->write($fileName, $expected);
		Assert::equal($expected, $this->fileManagerTest->read($fileName));
	}

}

$test = new JsonFileManagerTest($container);
$test->run();
