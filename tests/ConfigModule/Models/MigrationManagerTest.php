<?php

/**
 * TEST: App\ConfigModule\Models\MigrationManager
 * @covers App\ConfigModule\Models\MigrationManager
 * @phpVersion >= 7.2
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
final class MigrationManagerTest extends TestCase {

	/**
	 * Path to a directory with IQRF Gateway Daemon's configuration
	 */
	private const CONFIG_PATH = __DIR__ . '/../../data/configuration/';

	/**
	 * Path to a temporary directory with IQRF Gateway Daemon's configuration
	 */
	private const CONFIG_TEMP_PATH = __DIR__ . '/../../temp/migrated-configuration/';

	/**
	 * Path to a directory with correct JSON schemas
	 */
	private const SCHEMA_PATH = __DIR__ . '/../../data/cfgSchemas/';

	/**
	 * Path to a directory with corrupted JSON schemas
	 */
	private const SCHEMA_CORRUPTED_PATH = __DIR__ . '/../../temp/cfgSchemas/';

	/**
	 * ZIP archive content type
	 */
	private const ZIP_CONTENT_TYPE = 'application/zip';

	/**
	 * Path to ZIP archive with IQRF Gateway Daemon's configuration
	 */
	private const ZIP_PATH  = __DIR__ . '/../../data/iqrf-gateway-configuration.zip';

	/**
	 * Path to a temporary ZIP archive with IQRF Gateway Daemon's configuration
	 */
	private const ZIP_TEMP_PATH = __DIR__ . '/../../temp/iqrf-gateway-configuration.zip';

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
	 * Test function to download IQRF Gateway Daemon's configuration in a ZIP archive
	 */
	public function testDownload(): void {
		$timestamp = (new DateTime())->format('c');
		$actual = $this->manager->download();
		$fileName = 'iqrf-gateway-configuration_' . $timestamp . '.zip';
		$path = '/tmp/' . $fileName;
		$expected = new FileResponse($path, $fileName, self::ZIP_CONTENT_TYPE, true);
		Assert::equal($expected, $actual);
		Assert::equal(self::ZIP_CONTENT_TYPE, $actual->getContentType());
		$files = $this->createList(self::CONFIG_PATH);
		$zipManager = new ZipArchiveManager($path, ZipArchive::CREATE);
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
	 * Test function to upload IQRF Gateway Daemon's configuration (incorrect MIME type)
	 */
	public function testUploadBadMime(): void {
		$filePath = self::CONFIG_TEMP_PATH . '/config.json';
		$file = [
			'name' => 'config.json',
			'type' => 'application/json',
			'tmp_name' => $filePath,
			'error' => UPLOAD_ERR_OK,
			'size' => filesize($filePath),
		];
		$fileUpload = new FileUpload($file);
		Assert::exception(function () use ($fileUpload): void {
			$this->manager->upload($fileUpload);
		}, InvalidConfigurationFormatException::class);
	}

	/**
	 * Test function to upload IQRF Gateway Daemon's configuration (success)
	 */
	public function testUploadSuccess(): void {
		$this->manager->upload($this->mockUploadedArchive());
		$expected = $this->createList(self::CONFIG_PATH);
		$actual = $this->createList(self::CONFIG_TEMP_PATH);
		Assert::same($expected, $actual);
	}

	/**
	 * Mock an uploaded configuration
	 * @return FileUpload Mocked file upload
	 */
	private function mockUploadedArchive(): FileUpload {
		$file = [
			'name' => 'iqrf-gateway-configuration.zip',
			'type' => self::ZIP_CONTENT_TYPE,
			'tmp_name' => self::ZIP_TEMP_PATH,
			'error' => UPLOAD_ERR_OK,
			'size' => filesize(self::ZIP_TEMP_PATH),
		];
		return new FileUpload($file);
	}

	/**
	 * Test function to validate IQRF Gateway Daemon's configuration (missing JSON schema)
	 */
	public function testValidateMissingJsonSchema(): void {
		FileSystem::delete(self::SCHEMA_CORRUPTED_PATH . 'schema__iqrf__MqttMessaging.json');
		$zipManager = new ZipArchiveManager(self::ZIP_PATH, ZipArchive::CREATE);
		Assert::true($this->managerCorrupted->validate($zipManager));
		$zipManager->close();
	}

	/**
	 * Test function to validate IQRF Gateway Daemon's configuration (success)
	 */
	public function testValidateSuccess(): void {
		$zipManager = new ZipArchiveManager(self::ZIP_PATH, ZipArchive::CREATE);
		Assert::true($this->manager->validate($zipManager));
		$zipManager->close();
	}

	/**
	 * Set up the test environment
	 */
	protected function setUp(): void {
		Environment::lock('migration', __DIR__ . '/../../temp/');
		$this->copyFiles();
		$commandStack = new CommandStack();
		$commandManager = new CommandManager(false, $commandStack);
		$this->fileManager = new FileManager(self::CONFIG_PATH, $commandManager);
		$schemaManager = new ComponentSchemaManager(self::SCHEMA_PATH, $commandManager);
		$schemaManagerCorrupted = new ComponentSchemaManager(self::SCHEMA_CORRUPTED_PATH, $commandManager);
		$commandStack = new CommandStack();
		$commandManagerMock = Mockery::mock(CommandManager::class, [false, $commandStack])->makePartial();
		$serviceManager = Mockery::mock(ServiceManager::class);
		$serviceManager->shouldReceive('restart');
		$this->manager = new MigrationManager(self::CONFIG_TEMP_PATH, $commandManagerMock, $schemaManager, $serviceManager);
		$this->managerCorrupted = new MigrationManager(self::CONFIG_TEMP_PATH, $commandManagerMock, $schemaManagerCorrupted, $serviceManager);
	}

	/**
	 * Copy files for testing
	 */
	private function copyFiles(): void {
		FileSystem::copy(self::SCHEMA_PATH, self::SCHEMA_CORRUPTED_PATH);
		FileSystem::copy(self::CONFIG_PATH, self::CONFIG_TEMP_PATH);
		FileSystem::copy(self::ZIP_PATH, self::ZIP_TEMP_PATH);
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
