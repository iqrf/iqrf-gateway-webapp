<?php

/**
 * TEST: App\CoreModule\Models\JsonFileManager
 * @covers App\CoreModule\Models\JsonFileManager
 * @phpVersion >= 7.1
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Integration\CoreModule\Model;

use App\CoreModule\Models\JsonFileManager;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for JSON file manager
 */
class JsonFileManagerTest extends TestCase {

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
	private $path = __DIR__ . '/../../../data/configuration/';

	/**
	 * @var string Directory with configuration files
	 */
	private $pathTest = __DIR__ . '/../../../temp/configuration/';

	/**
	 * Tests the function to get a directory with files
	 */
	public function testGetDirectory(): void {
		Assert::same($this->path, $this->manager->getDirectory());
	}

	/**
	 * Tests the function to delete the JSON file
	 */
	public function testDelete(): void {
		$fileName = 'test-delete';
		$this->managerTest->write($fileName, $this->manager->read($this->fileName));
		Assert::true($this->managerTest->exists($fileName));
		$this->managerTest->delete($fileName);
		Assert::false($this->managerTest->exists($fileName));
	}

	/**
	 * Tests the function to check if the JSON file exists (the file is not exist)
	 */
	public function testExistsFail(): void {
		Assert::false($this->manager->exists('nonsense'));
	}

	/**
	 * Tests the function to check if the JSON file exists (the file is exist)
	 */
	public function testExistsSuccess(): void {
		Assert::true($this->manager->exists($this->fileName));
	}

	/**
	 * Tests the function to read a JSON file
	 */
	public function testRead(): void {
		$text = FileSystem::read($this->path . $this->fileName . '.json');
		$expected = Json::decode($text, Json::FORCE_ARRAY);
		Assert::equal($expected, $this->manager->read($this->fileName));
	}

	/**
	 * Tests the function to write a JSON file
	 */
	public function testWrite(): void {
		$fileName = 'config-test';
		$expected = $this->manager->read($this->fileName);
		$this->managerTest->write($fileName, $expected);
		Assert::equal($expected, $this->managerTest->read($fileName));
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$this->manager = new JsonFileManager($this->path);
		$this->managerTest = new JsonFileManager($this->pathTest);
	}

}

$test = new JsonFileManagerTest();
$test->run();
