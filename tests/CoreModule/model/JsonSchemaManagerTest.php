<?php

/**
 * TEST: App\CoreModule\Model\JsonSchemaManager
 * @covers App\CoreModule\Model\JsonSchemaManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\CoreModule\Model;

use App\CoreModule\Exception\InvalidJsonException;
use App\CoreModule\Exception\NonExistingJsonSchemaException;
use App\CoreModule\Model\JsonFileManager;
use App\CoreModule\Model\JsonSchemaManager;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

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
	private $filePath = __DIR__ . '/../../data/configuration/';

	/**
	 * @var JsonSchemaManager JSON schema manager
	 */
	private $manager;

	/**
	 * @var string Directory with JSON schemas
	 */
	private $schemaPath = __DIR__ . '/../../data/cfgSchemas/';

	/**
	 * Constructor
	 * @param Container $container Nette Tester Container
	 */
	public function __construct(Container $container) {
		$this->container = $container;
	}

	/**
	 * Set up the test environment
	 */
	protected function setUp() {
		$this->fileManager = new JsonFileManager($this->filePath);
		$this->manager = new JsonSchemaManager($this->schemaPath);
	}

	/**
	 * Test function to set file name of JSON schema from component name (fail)
	 */
	public function testSetSchemaFromComponentFail() {
		Assert::exception(function () {
			$this->manager->setSchemaFromComponent('nonsense');
		}, NonExistingJsonSchemaException::class);
	}

	/**
	 * Test function to set file name of JSON schema from component name (success)
	 */
	public function testSetSchemaFromComponentSuccess() {
		Assert::null($this->manager->setSchemaFromComponent('iqrf::MqttMessaging'));
	}

	/**
	 * Test function to validate JSON (invalid JSON)
	 */
	public function testValidateInvalid() {
		$this->manager->setSchemaFromComponent('iqrf::MqttMessaging');
		Assert::exception(function () {
			$json = (object) $this->fileManager->read('iqrf__MqMessaging');
			$this->manager->validate($json);
		}, InvalidJsonException::class);
	}

	/**
	 * Test function to validate JSON (valid JSON)
	 */
	public function testValidateValid() {
		$this->manager->setSchemaFromComponent('iqrf::MqttMessaging');
		$json = (object) $this->fileManager->read('iqrf__MqttMessaging');
		Assert::true($this->manager->validate($json));
	}

}

$test = new JsonSchemaManagerTest($container);
$test->run();
