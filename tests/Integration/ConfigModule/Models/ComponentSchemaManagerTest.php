<?php

/**
 * TEST: App\ConfigModule\Models\ComponentSchemaManager
 * @covers App\ConfigModule\Models\ComponentSchemaManager
 * @phpVersion >= 7.1
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Integration\ConfigModule\Models;

use App\ConfigModule\Models\ComponentSchemaManager;
use App\CoreModule\Entities\CommandStack;
use App\CoreModule\Exceptions\InvalidJsonException;
use App\CoreModule\Exceptions\NonexistentJsonSchemaException;
use App\CoreModule\Models\CommandManager;
use App\CoreModule\Models\JsonFileManager;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for component JSON file manager
 */
class ComponentSchemaManagerTest extends TestCase {

	/**
	 * MQTT component name
	 */
	private const COMPONENT_NAME = 'iqrf::MqttMessaging';

	/**
	 * Directory with configuration files
	 */
	private const FILE_PATH = __DIR__ . '/../../../data/configuration/';

	/**
	 * JSON schema directory path
	 */
	private const SCHEMA_PATH = __DIR__ . '/../../../data/cfgSchemas/';

	/**
	 * @var JsonFileManager JSON File manager
	 */
	private $fileManager;

	/**
	 * @var ComponentSchemaManager Component JSON schema manager
	 */
	private $manager;

	/**
	 * Tests the function to set file name of JSON schema from component name (fail)
	 */
	public function testSetSchemaFail(): void {
		Assert::exception(function (): void {
			$this->manager->setSchema('nonsense');
		}, NonexistentJsonSchemaException::class);
	}

	/**
	 * Tests the function to set file name of JSON schema from component name (success)
	 */
	public function testSetSchemaSuccess(): void {
		Assert::noError(function (): void {
			$this->manager->setSchema(self::COMPONENT_NAME);
		});
	}

	/**
	 * Tests the function to validate a JSON (invalid JSON)
	 */
	public function testValidateInvalid(): void {
		$this->manager->setSchema(self::COMPONENT_NAME);
		Assert::exception(function (): void {
			$json = (object) $this->fileManager->read('iqrf__MqMessaging');
			$this->manager->validate($json);
		}, InvalidJsonException::class);
	}

	/**
	 * Tests the function to validate a JSON (valid JSON)
	 */
	public function testValidateValid(): void {
		Assert::noError(function (): void {
			$this->manager->setSchema(self::COMPONENT_NAME);
			$json = (object) $this->fileManager->read('iqrf__MqttMessaging');
			$this->manager->validate($json);
		});
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$commandStack = new CommandStack();
		$commandManager = new CommandManager(false, $commandStack);
		$this->fileManager = new JsonFileManager(self::FILE_PATH, $commandManager);
		$this->manager = new ComponentSchemaManager(self::SCHEMA_PATH, $commandManager);
	}

}

$test = new ComponentSchemaManagerTest();
$test->run();
