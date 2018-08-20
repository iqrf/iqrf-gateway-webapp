<?php

/**
 * TEST: App\Model\FileManager
 * @covers App\Model\FileManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\Model;

use App\Model\FileManager;
use Nette\DI\Container;
use Nette\Utils\FileSystem;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../bootstrap.php';

/**
 * Tests for text file manager
 */
class FileManagerTest extends TestCase {

	/**
	 * @var Container Nette Tester Container
	 */
	private $container;

	/**
	 * @var string FIle name
	 */
	private $fileName = 'config.json';

	/**
	 * @var FileManager Text file manager
	 */
	private $manager;

	/**
	 * @var FileManager Text file manager
	 */
	private $managerTest;

	/**
	 * @var string Directory with configuration files
	 */
	private $path = __DIR__ . '/../data/configuration/';

	/**
	 * @var string Directory with configuration files
	 */
	private $pathTest = __DIR__ . '/../temp/configuration/';

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
		$this->manager = new FileManager($this->path);
		$this->managerTest = new FileManager($this->pathTest);
	}

	/**
	 * Test function to get directory with files
	 */
	public function testGetDirectory() {
		Assert::same($this->path, $this->manager->getDirectory());
	}

	/**
	 * Test function to delete file
	 */
	public function testDelete() {
		$fileName = 'test-delete.json';
		$this->managerTest->write($fileName, $this->manager->read($this->fileName));
		Assert::true($this->managerTest->exists($fileName));
		$this->managerTest->delete($fileName);
		Assert::false($this->managerTest->exists($fileName));
	}

	/**
	 * Test function to check if file exists (file is not exist)
	 */
	public function testExistsFail() {
		Assert::false($this->manager->exists('nonsense'));
	}

	/**
	 * Test function to check if file exists (file is exist)
	 */
	public function testExistsSucecss() {
		Assert::true($this->manager->exists($this->fileName));
	}

	/**
	 * Test function to read text file
	 */
	public function testRead() {
		$expected = FileSystem::read($this->path . $this->fileName);
		Assert::equal($expected, $this->manager->read($this->fileName));
	}

	/**
	 * Test function to write text file
	 */
	public function testWrite() {
		$fileName = 'config-test.json';
		$expected = $this->manager->read($this->fileName);
		$this->managerTest->write($fileName, $expected);
		Assert::equal($expected, $this->managerTest->read($fileName));
	}

}

$test = new FileManagerTest($container);
$test->run();
