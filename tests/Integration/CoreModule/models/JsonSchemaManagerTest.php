<?php

/**
 * TEST: App\CoreModule\Models\JsonSchemaManager
 * @covers App\CoreModule\Models\JsonSchemaManager
 * @phpVersion >= 7.1
 * @testCase
 */
declare(strict_types = 1);

namespace Test\Integration\CoreModule\Model;

use App\CoreModule\Exceptions\InvalidJsonException;
use App\CoreModule\Exceptions\NonExistingJsonSchemaException;
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
	 * @var JsonFileManager JSON File manager
	 */
	private $fileManager;

	/**
	 * @var string Directory with configuration files
	 */
	private $filePath = __DIR__ . '/../../../data/configuration/';

	/**
	 * @var JsonSchemaManager JSON schema manager
	 */
	private $manager;

	/**
	 * @var string Directory with JSON schemas
	 */
	private $schemaPath = __DIR__ . '/../../../data/cfgSchemas/';

	/**
	 * Tests the function to set file name of JSON schema from component name (fail)
	 */
	public function testSetSchemaFromComponentFail(): void {
		Assert::exception(function (): void {
			$this->manager->setSchemaFromComponent('nonsense');
		}, NonExistingJsonSchemaException::class);
	}

	/**
	 * Tests the function to set file name of JSON schema from component name (success)
	 */
	public function testSetSchemaFromComponentSuccess(): void {
		Assert::noError(function (): void {
			$this->manager->setSchemaFromComponent('iqrf::MqttMessaging');
		});
	}

	/**
	 * Tests the function to validate a JSON (invalid JSON)
	 */
	public function testValidateInvalid(): void {
		$this->manager->setSchemaFromComponent('iqrf::MqttMessaging');
		Assert::exception(function (): void {
			$json = (object) $this->fileManager->read('iqrf__MqMessaging');
			$this->manager->validate($json);
		}, InvalidJsonException::class);
	}

	/**
	 * Tests the function to validate a JSON (valid JSON)
	 */
	public function testValidateValid(): void {
		$this->manager->setSchemaFromComponent('iqrf::MqttMessaging');
		$json = (object) $this->fileManager->read('iqrf__MqttMessaging');
		Assert::true($this->manager->validate($json));
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$this->fileManager = new JsonFileManager($this->filePath);
		$this->manager = new JsonSchemaManager($this->schemaPath);
	}

}

$test = new JsonSchemaManagerTest();
$test->run();
