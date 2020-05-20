<?php

/**
 * TEST: App\ConfigModule\Models\SchedulerMigrationManager
 * @covers App\ConfigModule\Models\SchedulerMigrationManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\ConfigModule\Models;

use App\ConfigModule\Models\MainManager;
use App\ConfigModule\Models\SchedulerMigrationManager;
use App\ConfigModule\Models\SchedulerSchemaManager;
use App\CoreModule\Entities\CommandStack;
use App\CoreModule\Models\CommandManager;
use App\CoreModule\Models\FileManager;
use App\CoreModule\Models\ZipArchiveManager;
use DateTime;
use Mockery;
use Nette\Application\Responses\FileResponse;
use Nette\Http\FileUpload;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder;
use Tester\Assert;
use Tester\Environment;
use Tester\TestCase;
use Throwable;
use ZipArchive;

require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for scheduler's configuration migration manager
 */
class SchedulerMigrationManagerTest extends TestCase {

	/**
	 * @var string Path to a directory with scheduler's configuration
	 */
	private $configPath = __DIR__ . '/../../data/scheduler/';

	/**
	 * @var string Path to a temporary directory with scheduler's configuration
	 */
	private $configTempPath = __DIR__ . '/../../temp/migrations/scheduler/';

	/**
	 * @var FileManager Text file manager
	 */
	private $fileManager;

	/**
	 * @var SchedulerMigrationManager Configuration migration manager
	 */
	private $manager;

	/**
	 * @var string Path to a temporary ZIP archive with IQRF Gateway Daemon's configuration
	 */
	private $tempPath = __DIR__ . '/../../temp/iqrf-gateway-scheduler.zip';

	/**
	 * Test function to download IQRF Gateway Daemon's configuration in a ZIP archive
	 */
	public function testDownload(): void {
		try {
			$timestamp = '_' . (new DateTime())->format('c');
		} catch (Throwable $e) {
			$timestamp = '';
		}
		$actual = $this->manager->download();
		$fileName = 'iqrf-gateway-scheduler' . $timestamp . '.zip';
		$contentType = 'application/zip';
		$expected = new FileResponse('/tmp/' . $fileName, $fileName, $contentType, true);
		Assert::equal($expected, $actual);
		$files = $this->createList($this->configPath);
		$zipManager = new ZipArchiveManager('/tmp/' . $fileName, ZipArchive::CREATE);
		foreach ($files as $file) {
			$expected = $this->fileManager->read($file);
			Assert::same($expected, $zipManager->openFile($file));
		}
		$zipManager->close();
	}

	/**
	 * Create list of files
	 * @param string $path Path to the directory
	 * @return array<string> List of files in the directory
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
	 * Test function to upload IQRF Gateway Daemon's configuration (success)
	 */
	public function testUploadSuccess(): void {
		$this->manager->upload($this->mockUploadedArchive());
		$expected = $this->createList($this->configPath);
		$actual = $this->createList($this->configTempPath);
		Assert::same($expected, $actual);
	}

	/**
	 * Mock an uploaded configuration
	 * @return FileUpload Mocked file upload
	 */
	private function mockUploadedArchive(): FileUpload {
		$file = [
			'name' => 'iqrf-gateway-scheduler.zip',
			'type' => 'application/zip',
			'tmp_name' => $this->tempPath,
			'error' => UPLOAD_ERR_OK,
			'size' => filesize($this->tempPath),
		];
		return new FileUpload($file);
	}

	/**
	 * Set up the test environment
	 */
	protected function setUp(): void {
		Environment::lock('migration', __DIR__ . '/../../temp/');
		$this->copyFiles();
		$commandStack = new CommandStack();
		$commandManager = new CommandManager(false, $commandStack);
		$this->fileManager = new FileManager($this->configPath, $commandManager);
		$mainConfigManager = Mockery::mock(MainManager::class);
		$mainConfigManager->shouldReceive('getCacheDir')->andReturn($this->configTempPath . '/..');
		$schemaManager = Mockery::mock(SchedulerSchemaManager::class);
		$schemaManager->shouldReceive('validate')->andReturn(true);
		$commandManager = Mockery::mock(CommandManager::class);
		$this->manager = new SchedulerMigrationManager($mainConfigManager, $schemaManager, $commandManager);
	}

	/**
	 * Copy files for testing
	 */
	private function copyFiles(): void {
		FileSystem::copy($this->configPath, $this->configTempPath);
		$zipSource = __DIR__ . '/../../data/iqrf-gateway-scheduler.zip';
		FileSystem::copy($zipSource, $this->tempPath);
	}

	/**
	 * Cleanup the test environment
	 */
	protected function tearDown(): void {
		Mockery::close();
	}

}

$test = new SchedulerMigrationManagerTest();
$test->run();
