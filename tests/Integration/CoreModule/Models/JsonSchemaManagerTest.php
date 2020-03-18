<?php

/**
 * TEST: App\CoreModule\Models\JsonSchemaManager
 * @covers App\CoreModule\Models\JsonSchemaManager
 * @phpVersion >= 7.1
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Integration\CoreModule\Models;

use App\CoreModule\Entities\CommandStack;
use App\CoreModule\Exceptions\InvalidJsonException;
use App\CoreModule\Exceptions\NonExistingJsonSchemaException;
use App\CoreModule\Models\CommandManager;
use App\CoreModule\Models\JsonFileManager;
use App\CoreModule\Models\JsonSchemaManager;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for JSON file manager
 */
class JsonSchemaManagerTest extends TestCase {

	/**
	 * Directory with configuration files
	 */
	private const FILE_PATH = __DIR__ . '/../../../data/configuration/';

	/**
	 * JSON schema file name
	 */
	private const SCHEMA_NAME = 'schema__iqrf__MqttMessaging';

	/**
	 * JSON schema directory path
	 */
	private const SCHEMA_PATH = __DIR__ . '/../../../data/cfgSchemas/';

	/**
	 * @var JsonFileManager JSON File manager
	 */
	private $fileManager;

	/**
	 * @var JsonSchemaManager JSON schema manager
	 */
	private $manager;

	/**
	 * Tests the function to set file name of JSON schema from component name (fail)
	 */
	public function testSetSchemaFail(): void {
		Assert::exception(function (): void {
			$this->manager->setSchema('nonsense');
		}, NonExistingJsonSchemaException::class);
	}

	/**
	 * Tests the function to set file name of JSON schema from component name (success)
	 */
	public function testSetSchemaSuccess(): void {
		Assert::noError(function (): void {
			$this->manager->setSchema(self::SCHEMA_NAME);
		});
	}

	/**
	 * Tests the function to validate a JSON (invalid JSON)
	 */
	public function testValidateInvalid(): void {
		$this->manager->setSchema(self::SCHEMA_NAME);
		Assert::exception(function (): void {
			$json = (object) $this->fileManager->read('iqrf__MqMessaging');
			$this->manager->validate($json);
		}, InvalidJsonException::class);
	}

	/**
	 * Tests the function to validate a JSON (invalid format)
	 */
	public function testValidateInvalidFormat(): void {
		$this->manager->setSchema(self::SCHEMA_NAME);
		Assert::exception(function (): void {
			$this->manager->validate('nonsense');
		}, InvalidJsonException::class);
	}

	/**
	 * Tests the function to validate a JSON (valid JSON)
	 */
	public function testValidateValid(): void {
		$this->manager->setSchema(self::SCHEMA_NAME);
		$json = (object) $this->fileManager->read('iqrf__MqttMessaging');
		Assert::true($this->manager->validate($json));
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$commandStack = new CommandStack();
		$commandManager = new CommandManager(false, $commandStack);
		$this->fileManager = new JsonFileManager(self::FILE_PATH, $commandManager);
		$this->manager = new JsonSchemaManager(self::SCHEMA_PATH, $commandManager);
	}

}

$test = new JsonSchemaManagerTest();
$test->run();
