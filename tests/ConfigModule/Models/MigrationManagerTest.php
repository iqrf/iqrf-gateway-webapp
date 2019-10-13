<?php

/**
 * TEST: App\ConfigModule\Models\MigrationManager
 * @covers App\ConfigModule\Models\MigrationManager
 * @phpVersion >= 7.1
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\ConfigModule\Models;

use App\ConfigModule\Exceptions\InvalidConfigurationFormatException;
use App\ConfigModule\Models\ComponentSchemaManager;
use App\ConfigModule\Models\MigrationManager;
use App\CoreModule\Entities\CommandStack;
use App\CoreModule\Models\CommandManager;
use App\CoreModule\Models\FileManager;
use App\CoreModule\Models\ZipArchiveManager;
use App\ServiceModule\Models\ServiceManager;
use DateTime;
use Mockery;
use Mockery\MockInterface;
use Nette\Application\Responses\FileResponse;
use Nette\Http\FileUpload;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder;
use Tester\Assert;
use Tester\Environment;
use Tester\TestCase;
use ZipArchive;

require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for IQRF interface manager
 */
class MigrationManagerTest extends TestCase {

	/**
	 * @var CommandManager|MockInterface Mocker command manager
	 */
	private $commandManager;

	/**
	 * @var string Path to a directory with IQRF Gateway Daemon's configuration
	 */
	private $configPath = __DIR__ . '/../../data/configuration/';

	/**
	 * @var string Path to a temporary directory with IQRF Gateway Daemon's configuration
	 */
	private $configTempPath = __DIR__ . '/../../temp/migrated-configuration/';

	/**
	 * @var FileManager Text file manager
	 */
	private $fileManager;

	/**
	 * @var MigrationManager Configuration migration manager
	 */
	private $manager;

	/**
	 * @var MigrationManager Configuration migration manager (with corrupted JSON schemas)
	 */
	private $managerCorrupted;

	/**
	 * @var string Path to the ZIP archive with IQRF Gateway Daemon's configuration
	 */
	private $path = '/tmp/iqrf-daemon-configuration.zip';

	/**
	 * @var string Path to a directory with correct JSON schemas
	 */
	private $schemaPath = __DIR__ . '/../../data/cfgSchemas/';

	/**
	 * @var string Path to a directory with corrupted JSON schemas
	 */
	private $schemaCorruptedPath = __DIR__ . '/../../temp/cfgSchemas/';

	/**
	 * @var string Path to a temporary ZIP archive with IQRF Gateway Daemon's configuration
	 */
	private $tempPath = __DIR__ . '/../../temp/iqrf-gateway-configuration.zip';

	/**
	 * Test function to download IQRF Gateway Daemon's configuration in a ZIP archive
	 */
	public function testDownload(): void {
		$timestamp = (new DateTime())->format('c');
		$actual = $this->manager->download();
		$fileName = 'iqrf-gateway-configuration_' . $timestamp . '.zip';
		$contentType = 'application/zip';
		$expected = new FileResponse($this->path, $fileName, $contentType, true);
		Assert::equal($expected, $actual);
		$files = $this->createList($this->configPath);
		$zipManager = new ZipArchiveManager($this->path, ZipArchive::CREATE);
		foreach ($files as $file) {
			$expected = $this->fileManager->read($file);
			Assert::same($expected, $zipManager->openFile($file));
		}
		$zipManager->close();
	}

	/**
	 * Create list of files
	 * @param string $path Path to the directory
	 * @return string[] List of files in the directory
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
	 * Test function to upload IQRF Gateway Daemon's configuration (incorrect MIME type)
	 */
	public function testUploadBadMime(): void {
		$filePath = $this->configTempPath . '/config.json';
		$file = [
			'name' => 'config.json',
			'type' => 'application/json',
			'tmp_name' => $filePath,
			'error' => UPLOAD_ERR_OK,
			'size' => filesize($filePath),
		];
		$values = ['configuration' => new FileUpload($file)];
		Assert::exception(function () use ($values): void {
			$this->manager->upload($values);
		}, InvalidConfigurationFormatException::class);
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
	 * @return FileUpload[] Mocked form values
	 */
	private function mockUploadedArchive(): array {
		$values = [];
		$file = [
			'name' => 'iqrf-gateway-configuration.zip',
			'type' => 'application/zip',
			'tmp_name' => $this->tempPath,
			'error' => UPLOAD_ERR_OK,
			'size' => filesize($this->tempPath),
		];
		$values['configuration'] = new FileUpload($file);
		return $values;
	}

	/**
	 * Test function to validate IQRF Gateway Daemon's configuration (missing JSON schema)
	 */
	public function testValidateMissingJsonSchema(): void {
		FileSystem::delete($this->schemaCorruptedPath . 'schema__iqrf__MqttMessaging.json');
		$path = __DIR__ . '/../data/iqrf-gateway-configuration.zip';
		$zipManager = new ZipArchiveManager($path, ZipArchive::CREATE);
		Assert::true($this->managerCorrupted->validate($zipManager));
		$zipManager->close();
	}

	/**
	 * Test function to validate IQRF Gateway Daemon's configuration (success)
	 */
	public function testValidateSuccess(): void {
		$path = __DIR__ . '/../data/iqrf-gateway-configuration.zip';
		$zipManager = new ZipArchiveManager($path, ZipArchive::CREATE);
		Assert::true($this->manager->validate($zipManager));
		$zipManager->close();
	}

	/**
	 * Set up the test environment
	 */
	protected function setUp(): void {
		Environment::lock('migration', __DIR__ . '/../../temp/');
		$this->copyFiles();
		$this->fileManager = new FileManager($this->configPath);
		$schemaManager = new ComponentSchemaManager($this->schemaPath);
		$schemaManagerCorrupted = new ComponentSchemaManager($this->schemaCorruptedPath);
		$commandStack = new CommandStack();
		$this->commandManager = Mockery::mock(CommandManager::class, [false, $commandStack])->makePartial();
		$serviceManager = Mockery::mock(ServiceManager::class);
		$serviceManager->shouldReceive('restart');
		$this->manager = new MigrationManager($this->configTempPath, $this->commandManager, $schemaManager, $serviceManager);
		$this->managerCorrupted = new MigrationManager($this->configTempPath, $this->commandManager, $schemaManagerCorrupted, $serviceManager);
	}

	/**
	 * Copy files for testing
	 */
	private function copyFiles(): void {
		FileSystem::copy($this->schemaPath, $this->schemaCorruptedPath);
		FileSystem::copy($this->configPath, $this->configTempPath);
		$zipSource = __DIR__ . '/../../data/iqrf-gateway-configuration.zip';
		FileSystem::copy($zipSource, $this->tempPath);
	}

	/**
	 * Cleanup the test environment
	 */
	protected function tearDown(): void {
		Mockery::close();
	}

}

$test = new MigrationManagerTest();
$test->run();
