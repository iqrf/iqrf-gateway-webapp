<?php

/**
 * TEST: App\CoreModule\Model\ZipArchiveManager
 * @covers App\CoreModule\Model\ZipArchiveManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\CoreModule\Model;

use App\CoreModule\Model\ZipArchiveManager;
use Nette\DI\Container;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for ZIP archive manager manager
 */
class ZipArchiveManagerTest extends TestCase {

	/**
	 * @var string Path to the directory with IQRF Gateway Daemon's configuration
	 */
	private $configDir = __DIR__ . '/../../data/configuration/';

	/**
	 * @var Container Nette Tester Container
	 */
	private $container;

	/**
	 * @var ZipArchiveManager ZIP archive manager for new archive creation
	 */
	private $managerNew;

	/**
	 * @var ZipArchiveManager ZIP archive manager for extraction
	 */
	private $manager;

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
	protected function setUp() {
		$path = __DIR__ . '/../../temp/archive.zip';
		$pathExtract = __DIR__ . '/../../data/iqrf-gateway-configuration.zip';
		$this->managerNew = new ZipArchiveManager($path);
		$this->manager = new ZipArchiveManager($pathExtract, \ZipArchive::CREATE);
	}

	/**
	 * Cleanup the test environment
	 */
	protected function tearDown() {
		@$this->managerNew->close();
		@$this->manager->close();
	}

	/**
	 * Create list of files
	 * @param string $path Path to the directory
	 * @return array List of files in the directory
	 */
	private function createList(string $path): array {
		$path = realpath($path) . '/';
		$list = [];
		foreach (Finder::findFiles('*.json')->from($path) as $file) {
			$list[] = str_replace($path, '', $file->getRealPath());
		}
		sort($list);
		return $list;
	}

	/**
	 * Test function to add file to the ZIP archive
	 */
	public function testAddFile() {
		$fileName = 'config.json';
		$path = $this->configDir . $fileName;
		$this->managerNew->addFile($path, $fileName);
		Assert::same([$fileName], $this->managerNew->listFiles());
	}

	/**
	 * Test function to add text file to the ZIP archive
	 */
	public function testAddFileFromText() {
		$fileName = 'test.json';
		$text = 'Test';
		$this->managerNew->addFileFromText($fileName, $text);
		Assert::same([$fileName], $this->managerNew->listFiles());
	}

	/**
	 * Test function to add a directory into the ZIP archive
	 */
	public function testAddFolder() {
		$this->managerNew->addFolder($this->configDir, '');
		$expected = $this->createList($this->configDir);
		Assert::same($expected, $this->managerNew->listFiles());
	}

	/**
	 * Test function to add a JSON file to the ZIP archive
	 */
	public function testAddJsonFromArray() {
		$array = [
			'status' => 'OK',
		];
		$fileName = 'test.json';
		$this->managerNew->addJsonFromArray($fileName, $array);
		Assert::same([$fileName], $this->managerNew->listFiles());
	}

	/**
	 * Test function to check if the file(s) exist(s) in the archive (a non-existing file)
	 */
	public function testExistNonexistingFile() {
		Assert::false($this->manager->exist('nonsense'));
	}

	/**
	 * Test function to check if the file(s) exist(s) in the archive (a single file)
	 */
	public function testExistFile() {
		Assert::true($this->manager->exist('config.json'));
	}

	/**
	 * Test function to check if the file(s) exist(s) in the archive (a single file in a subdirectory)
	 */
	public function testExistFileInSubdir() {
		Assert::true($this->manager->exist('scheduler/Tasks.json'));
	}

	/**
	 * Test function to check if the file(s) exist(s) in the archive (multiple files)
	 */
	public function testExistFiles() {
		$files = $this->createList($this->configDir);
		Assert::true($this->manager->exist($files));
	}

	/**
	 * Test function to extract the archive content
	 */
	public function testExtract() {
		$originalPath = realpath($this->configDir);
		$destinationPath = realpath(__DIR__ . '/../../temp/zip');
		$this->manager->extract($destinationPath);
		$expected = $this->createList($originalPath);
		$actual = $this->createList($destinationPath);
		Assert::same($expected, $actual);
		$expectedFile = FileSystem::read($originalPath . '/config.json');
		$actualFile = FileSystem::read($destinationPath . '/config.json');
		Assert::same($expectedFile, $actualFile);
	}

	/**
	 * Test function to list files in the archive
	 */
	public function testListFiles() {
		$expected = $this->createList($this->configDir);
		Assert::same($expected, $this->manager->listFiles());
	}

	/**
	 * Test function to open a file in the archive
	 */
	public function testOpenFile() {
		$fileName = 'config.json';
		$expected = FileSystem::read($this->configDir . $fileName);
		Assert::same($expected, $this->manager->openFile($fileName));
	}

}

$test = new ZipArchiveManagerTest($container);
$test->run();
