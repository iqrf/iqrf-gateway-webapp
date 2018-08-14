<?php

/**
 * TEST: App\Model\JsonSchemaManager
 * @covers App\Model\JsonSchemaManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\Model;

use App\Model\InvalidJson;
use App\Model\JsonFileManager;
use App\Model\JsonSchemaManager;
use App\Model\NonExistingJsonSchema;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../bootstrap.php';

/**
 * Tests for JSON file manager
 */
class JsonSchemaManagerTest extends TestCase {

	/**
	 * @var Container Nette Tester Container
	 */
	private $container;

	/**
	 * @var JsonFileManager JSON File manager
	 */
	private $fileManager;

	/**
	 * @var string Directory with configuration files
	 */
	private $filePath = __DIR__ . '/../configuration/';

	/**
	 * @var JsonSchemaManager JSON schema manager
	 */
	private $manager;

	/**
	 * @var string Directory with JSON schemas
	 */
	private $schemaPath = __DIR__ . '/../jsonschema/';

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
	public function setUp() {
		$this->fileManager = new JsonFileManager($this->filePath);
		$this->manager = new JsonSchemaManager($this->schemaPath);
	}

	/**
	 * Test function to set file name of JSON schema from component name
	 */
	public function testSetSchemaFromComponent() {
		Assert::null($this->manager->setSchemaFromComponent('iqrf::MqttMessaging'));
		Assert::exception(function () {
			$this->manager->setSchemaFromComponent('nonsense');
		}, NonExistingJsonSchema::class);
	}

	/**
	 * Test function to validate JSON
	 */
	public function testValidate() {
		$this->manager->setSchemaFromComponent('iqrf::MqttMessaging');
		$json = (object) $this->fileManager->read('iqrf__MqttMessaging');
		Assert::true($this->manager->validate($json));
		Assert::exception(function () {
			$json = (object) $this->fileManager->read('iqrf__MqMessaging');
			$this->manager->validate($json);
		}, InvalidJson::class);
	}

}

$test = new JsonSchemaManagerTest($container);
$test->run();
