<?php

/**
 * TEST: App\CoreModule\Models\JsonSchemaManager
 * @covers App\CoreModule\Models\JsonSchemaManager
 * @phpVersion >= 7.4
 * @testCase
 */
/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
declare(strict_types = 1);

namespace Tests\Integration\CoreModule\Models;

use App\CoreModule\Entities\CommandStack;
use App\CoreModule\Exceptions\InvalidJsonException;
use App\CoreModule\Exceptions\NonexistentJsonSchemaException;
use App\CoreModule\Models\CommandManager;
use App\CoreModule\Models\FileManager;
use App\CoreModule\Models\JsonSchemaManager;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for JSON schema manager
 */
final class JsonSchemaManagerTest extends TestCase {

	/**
	 * @var string JSON file name
	 */
	private const FILE_NAME = 'iqrf__MqttMessaging.json';

	/**
	 * @var string Directory with configuration files
	 */
	private const FILE_PATH = TESTER_DIR . '/data/configuration/';

	/**
	 * @var string JSON schema file name
	 */
	private const SCHEMA_NAME = 'schema__iqrf__MqttMessaging';

	/**
	 * @var string JSON schema directory path
	 */
	private const SCHEMA_PATH = TESTER_DIR . '/data/cfgSchemas/';

	/**
	 * @var FileManager File manager
	 */
	private FileManager $fileManager;

	/**
	 * @var JsonSchemaManager JSON schema manager
	 */
	private JsonSchemaManager $manager;

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
			$this->manager->setSchema(self::SCHEMA_NAME);
		});
	}

	/**
	 * Tests the function to validate a JSON (invalid JSON)
	 */
	public function testValidateInvalid(): void {
		$this->manager->setSchema(self::SCHEMA_NAME);
		Assert::exception(function (): void {
			$json = (object) $this->fileManager->readJson('iqrf__MqMessaging.json');
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
		Assert::noError(function (): void {
			$this->manager->setSchema(self::SCHEMA_NAME);
			$json = (object) $this->fileManager->readJson(self::FILE_NAME);
			$this->manager->validate($json);
		});
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$commandStack = new CommandStack();
		$commandManager = new CommandManager(false, $commandStack);
		$this->fileManager = new FileManager(self::FILE_PATH, $commandManager);
		$this->manager = new JsonSchemaManager(self::SCHEMA_PATH, $commandManager);
	}

}

$test = new JsonSchemaManagerTest();
$test->run();
