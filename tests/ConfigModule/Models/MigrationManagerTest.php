<?php

/**
 * TEST: App\ConfigModule\Models\MigrationManager
 * @covers App\ConfigModule\Models\MigrationManager
 * @phpVersion >= 7.2
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\ConfigModule\Models;

use App\ConfigModule\Models\ComponentSchemaManager;
use App\ConfigModule\Models\MigrationManager;
use App\CoreModule\Entities\CommandStack;
use App\CoreModule\Models\CommandManager;
use App\CoreModule\Models\FileManager;
use App\CoreModule\Models\ZipArchiveManager;
use App\GatewayModule\Models\Utils\GatewayInfoUtil;
use App\ServiceModule\Models\ServiceManager;
use Mockery;
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
	 * Path to a nonexistent directory with IQRF Gateway Daemon's cache
	 */
	private const CACHE_TEMP_PATH = __DIR__ . '/../../temp/migrated-configuration/cache/';

	/**
	 * Path to a directory with IQRF Gateway Daemon's configuration
	 */
	private const CONFIG_PATH = __DIR__ . '/../../data/configuration/';

	/**
	 * Path to a temporary directory with IQRF Gateway Daemon's configuration
	 */
	private const CONFIG_TEMP_PATH = __DIR__ . '/../../temp/migrated-configuration/';

	/**
	 * Path to nonexistant directory with IQRF Gateway Controller's configuration
	 */
	private const CONFIG_TEMP_PATH_CONTROLLER = __DIR__ . '/../../temp/migrated-configuration/controller';

	/**
	 * Path to nonexistant directory with IQRF Gateway Translator's configuration
	 */
	private const CONFIG_TEMP_PATH_TRANSLATOR = __DIR__ . '/../../temp/migrated-configuration/translator';

	/**
	 * Path to a directory with correct JSON schemas
	 */
	private const SCHEMA_PATH = __DIR__ . '/../../data/cfgSchemas/';

	/**
	 * Path to a directory with corrupted JSON schemas
	 */
	private const SCHEMA_CORRUPTED_PATH = __DIR__ . '/../../temp/cfgSchemas/';

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
	 * Test function to extract the archive with scheduler configuration
	 */
	public function testCreateArchive(): void {
		$actual = $this->manager->createArchive();
		$files = $this->createList(self::CONFIG_PATH);
		$zipManager = new ZipArchiveManager($actual, ZipArchive::CREATE);
		foreach ($files as $file) {
			$expected = $this->fileManager->read($file);
			Assert::same($expected, $zipManager->openFile('daemon/' . $file));
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
	 * Test function to extract the archive with scheduler configuration
	 */
	public function testExtractArchive(): void {
		$this->manager->extractArchive(self::ZIP_TEMP_PATH);
		$expected = $this->createList(self::CONFIG_PATH);
		$actual = $this->createList(self::CONFIG_TEMP_PATH);
		Assert::same($expected, $actual);
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
		$commandStack = new CommandStack();
		$commandManager = new CommandManager(false, $commandStack);
		$gwInfo = new GatewayInfoUtil($commandManager);
		$this->manager = new MigrationManager(self::CONFIG_TEMP_PATH, self::CACHE_TEMP_PATH, self::CONFIG_TEMP_PATH_CONTROLLER, self::CONFIG_TEMP_PATH_TRANSLATOR, $commandManagerMock, $schemaManager, $serviceManager, $gwInfo);
		$this->managerCorrupted = new MigrationManager(self::CONFIG_TEMP_PATH, self::CACHE_TEMP_PATH, self::CONFIG_TEMP_PATH_CONTROLLER, self::CONFIG_TEMP_PATH_TRANSLATOR, $commandManagerMock, $schemaManagerCorrupted, $serviceManager, $gwInfo);
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
