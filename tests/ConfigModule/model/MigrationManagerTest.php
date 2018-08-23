<?php

/**
 * TEST: App\ConfigModule\Model\MigrationManager
 * @covers App\ConfigModule\Model\MigrationManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\ConfigModule\Model;

use App\ConfigModule\Exception\InvalidConfigurationFormatException;
use App\ConfigModule\Model\MigrationManager;
use App\CoreModule\Model\CommandManager;
use App\CoreModule\Model\FileManager;
use App\CoreModule\Model\JsonSchemaManager;
use App\CoreModule\Model\ZipArchiveManager;
use Nette\Application\Responses\FileResponse;
use Nette\DI\Container;
use Nette\Http\FileUpload;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for IQRF interface manager
 */
class MigrationManagerTest extends TestCase {

	/**
	 * @var \Mockery\Mock Mocker command manager
	 */
	private $commandManager;

	/**
	 * @var Container Nette Tester Container
	 */
	private $container;

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
	 * @1var string Path to a temporary ZIP archive with IQRF Gateway Daemon's configuration
	 */
	private $tempPath = __DIR__ . '/../../temp/iqrf-gateway-configuration.zip';

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
	protected function setUp() {
		\Tester\Environment::lock('migration', __DIR__ . '/../../temp/');
		$this->copyFiles();
		$this->fileManager = new FileManager($this->configPath);
		$schemaManager = new JsonSchemaManager($this->schemaPath);
		$schemaManagerCorrupted = new JsonSchemaManager($this->schemaCorruptedPath);
		$this->commandManager = \Mockery::mock(CommandManager::class, [false])->makePartial();
		$this->manager = new MigrationManager($this->configTempPath, $this->commandManager, $schemaManager);
		$this->managerCorrupted = new MigrationManager($this->configTempPath, $this->commandManager, $schemaManagerCorrupted);
	}

	/**
	 * Copy files for testing
	 */
	private function copyFiles() {
		FileSystem::copy($this->schemaPath, $this->schemaCorruptedPath);
		FileSystem::copy($this->configPath, $this->configTempPath);
		$zipSource = __DIR__ . '/../../data/iqrf-gateway-configuration.zip';
		FileSystem::copy($zipSource, $this->tempPath);
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
	 * Mock an uploaded configuration
	 * @return array Mocked form values
	 */
	private function mockUploadedArchive(): array {
		$values = [];
		$filePath = __DIR__ . '/../../temp/iqrf-gateway-configuration.zip';
		$file = [
			'name' => 'iqrf-gateway-configuration.zip',
			'type' => 'application/zip',
			'tmp_name' => $filePath,
			'error' => UPLOAD_ERR_OK,
			'size' => filesize($filePath),
		];
		$values['configuration'] = new FileUpload($file);
		return $values;
	}

	/**
	 * Test function to download IQRF Gateway Daemon's configuration in a ZIP archive
	 */
	public function testDownload() {
		$timestamp = (new \DateTime())->format('c');
		$actual = $this->manager->download();
		$fileName = 'iqrf-gateway-configuration_' . $timestamp . '.zip';
		$contentType = 'application/zip';
		$expected = new FileResponse($this->path, $fileName, $contentType, true);
		Assert::equal($expected, $actual);
		$files = $this->createList($this->configPath);
		$zipManager = new ZipArchiveManager($this->path, \ZipArchive::CREATE);
		foreach ($files as $file) {
			$expected = $this->fileManager->read($file);
			Assert::same($expected, $zipManager->openFile($file));
		}
		$zipManager->close();
	}

	/**
	 * Test function to upload IQRF Gateway Daemon's configuration (incorrect MIME type)
	 */
	public function testUploadBadMime() {
		$filePath = $this->configTempPath . '/config.json';
		$file = [
			'name' => 'config.json',
			'type' => 'application/json',
			'tmp_name' => $filePath,
			'error' => UPLOAD_ERR_OK,
			'size' => filesize($filePath),
		];
		$values['configuration'] = new FileUpload($file);
		Assert::exception(function () use ($values) {
			$this->manager->upload($values);
		}, InvalidConfigurationFormatException::class);
	}

	/**
	 * Test function to upload IQRF Gateway Daemon's configuration (success)
	 */
	public function testUploadSuccess() {
		$this->manager->upload($this->mockUploadedArchive());
		$expectedFiles = $this->createList($this->configPath);
		sleep(2);
		$actualFiles = $this->createList($this->configTempPath);
		Assert::same($expectedFiles, $actualFiles);
	}

	/**
	 * Test function to validate IQRF Gateway Daemon's configuration (missing JSON schema)
	 */
	public function testValidateMissingJsonSchema() {
		FileSystem::delete($this->schemaCorruptedPath . 'schema__iqrf__MqttMessaging.json');
		$path = __DIR__ . '/../data/iqrf-gateway-configuration.zip';
		$zipManager = new ZipArchiveManager($path, \ZipArchive::CREATE);
		Assert::true($this->managerCorrupted->validate($zipManager));
		$zipManager->close();
	}

	/**
	 * Test function to validate IQRF Gateway Daemon's configuration (success)
	 */
	public function testValidateSuccess() {
		$path = __DIR__ . '/../data/iqrf-gateway-configuration.zip';
		$zipManager = new ZipArchiveManager($path, \ZipArchive::CREATE);
		Assert::true($this->manager->validate($zipManager));
		$zipManager->close();
	}

}

$test = new MigrationManagerTest($container);
$test->run();
