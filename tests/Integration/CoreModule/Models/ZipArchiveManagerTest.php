<?php

/**
 * TEST: App\CoreModule\Models\ZipArchiveManager
 * @covers App\CoreModule\Models\ZipArchiveManager
 * @phpVersion >= 7.2
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Integration\CoreModule\Models;

use App\CoreModule\Models\ZipArchiveManager;
use Nette\Utils\ArrayHash;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder;
use Tester\Assert;
use Tester\TestCase;
use ZipArchive;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for ZIP archive manager manager
 */
final class ZipArchiveManagerTest extends TestCase {

	/**
	 * Path to the directory with IQRF Gateway Daemon's configuration
	 */
	private const CONFIG_DIR = __DIR__ . '/../../../data/configuration/';

	/**
	 * File name
	 */
	private const FILE_NAME = 'config.json';

	/**
	 * @var ZipArchiveManager ZIP archive manager for new archive creation
	 */
	private $managerNew;

	/**
	 * @var ZipArchiveManager ZIP archive manager for extraction
	 */
	private $manager;

	/**
	 * Tests the function to add a file to the ZIP archive
	 */
	public function testAddFile(): void {
		$path = self::CONFIG_DIR . self::FILE_NAME;
		$this->managerNew->addFile($path, 'daemon/' . self::FILE_NAME);
		Assert::same(['daemon/' . self::FILE_NAME], $this->managerNew->listFiles());
	}

	/**
	 * Tests the function to add a text file to the ZIP archive
	 */
	public function testAddFileFromText(): void {
		$fileName = 'test.json';
		$text = 'Test';
		$this->managerNew->addFileFromText($fileName, $text);
		Assert::same([$fileName], $this->managerNew->listFiles());
	}

	/**
	 * Tests the function to add a directory into the ZIP archive
	 */
	public function testAddFolder(): void {
		$this->managerNew->addFolder(self::CONFIG_DIR, '');
		$expected = $this->createList(self::CONFIG_DIR);
		Assert::same($expected, $this->managerNew->listFiles());
	}

	/**
	 * Creates the list of files
	 * @param string $path Path to the directory
	 * @return array<string>|array List of files in the directory
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
	 * Tests the function to add a JSON file to the ZIP archive
	 */
	public function testAddJsonFromArray(): void {
		$array = [
			'status' => 'OK',
		];
		$fileName = 'test.json';
		$this->managerNew->addJsonFromArray($fileName, $array);
		Assert::same([$fileName], $this->managerNew->listFiles());
	}

	/**
	 * Tests the function to check if the file(s) exist(s) in the archive (a non-existing file)
	 */
	public function testExistNonExistingFile(): void {
		Assert::false($this->manager->exist('nonsense'));
	}

	/**
	 * Tests the function to check if the file(s) exist(s) in the archive (a single file)
	 */
	public function testExistFile(): void {
		Assert::true($this->manager->exist('daemon/' . self::FILE_NAME));
	}

	/**
	 * Tests the function to check if the file(s) exist(s) in the archive (a single file in a subdirectory)
	 */
	public function testExistFileInSubDir(): void {
		Assert::true($this->manager->exist('daemon/scheduler/Tasks.json'));
	}

	/**
	 * Tests the function to check if the file(s) exist(s) in the archive (multiple files)
	 */
	public function testExistFiles(): void {
		$files = $this->createList(self::CONFIG_DIR);
		$filesCount = count($files);
		for ($i = 0; $i < $filesCount; ++$i) {
			$files[$i] = 'daemon/' . $files[$i];
		}
		Assert::true($this->manager->exist(ArrayHash::from($files)));
	}

	/**
	 * Tests the function to extract the archive content
	 */
	public function testExtract(): void {
		$originalPath = realpath(self::CONFIG_DIR);
		$destinationPath = realpath(__DIR__ . '/../../../temp/zip');
		$this->manager->extract($destinationPath);
		$expected = $this->createList($originalPath);
		$expectedCount = count($expected);
		for ($i = 0; $i < $expectedCount; ++$i) {
			$expected[$i] = 'daemon/' . $expected[$i];
		}
		$actual = $this->createList($destinationPath);
		Assert::same($expected, $actual);
		$expectedFile = FileSystem::read($originalPath . '/' . self::FILE_NAME);
		$actualFile = FileSystem::read($destinationPath . '/daemon/' . self::FILE_NAME);
		Assert::same($expectedFile, $actualFile);
	}

	/**
	 * Tests the function to list files in the archive
	 */
	public function testListFiles(): void {
		$expected = $this->createList(self::CONFIG_DIR);
		$expectedCount = count($expected);
		for ($i = 0; $i < $expectedCount; ++$i) {
			$expected[$i] = 'daemon/' . $expected[$i];
		}
		Assert::same($expected, $this->manager->listFiles());
	}

	/**
	 * Tests the function to open a file in the archive
	 */
	public function testOpenFile(): void {
		$expected = FileSystem::read(self::CONFIG_DIR . self::FILE_NAME);
		Assert::same($expected, $this->manager->openFile('daemon/' . self::FILE_NAME));
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$path = __DIR__ . '/../../../temp/archive.zip';
		$pathExtract = __DIR__ . '/../../../data/iqrf-gateway-configuration.zip';
		$this->managerNew = new ZipArchiveManager($path);
		$this->manager = new ZipArchiveManager($pathExtract, ZipArchive::CREATE);
	}

	/**
	 * Cleanups the test environment
	 */
	protected function tearDown(): void {
		@$this->managerNew->close();
		@$this->manager->close();
	}

}

$test = new ZipArchiveManagerTest();
$test->run();
