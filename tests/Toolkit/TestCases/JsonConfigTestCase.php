<?php

/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
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

namespace Tests\Toolkit\TestCases;

use App\ConfigModule\Models\ComponentSchemaManager;
use App\CoreModule\Entities\CommandStack;
use App\CoreModule\Models\CommandManager;
use App\CoreModule\Models\JsonFileManager;
use Mockery;
use Nette\Utils\JsonException;
use Tester\Environment;
use Tester\TestCase;

/**
 * JSON configuration test case
 */
abstract class JsonConfigTestCase extends TestCase {

	/**
	 * @var JsonFileManager JSON file manager
	 */
	protected $fileManager;

	/**
	 * @var JsonFileManager JSON temporary file manager
	 */
	protected $fileManagerTemp;

	/**
	 * @var ComponentSchemaManager JSON schema manager
	 */
	protected $schemaManager;

	/**
	 * Copies the JSON file to a temporary directory
	 * @param string $fileName JSON file name to copy
	 */
	protected function copyFile(string $fileName): void {
		try {
			$content = $this->fileManager->read($fileName);
			$this->fileManagerTemp->write($fileName, $content);
		} catch (JsonException $e) {
			Environment::skip('JSON Exception: ' . $e->getMessage());
		}
	}

	/**
	 * Reads the JSON file
	 * @param string $fileName JSON file name to read
	 * @return array<mixed> JSON file's content
	 */
	protected function readFile(string $fileName): array {
		try {
			return $this->fileManager->read($fileName);
		} catch (JsonException $e) {
			Environment::skip('JSON Exception: ' . $e->getMessage());
			return [];
		}
	}

	/**
	 * Reads the temporary JSON file
	 * @param string $fileName Temporary JSON file name to read
	 * @return array<mixed> Temporary JSON file's content
	 * @throws JsonException
	 */
	protected function readTempFile(string $fileName): array {
		return $this->fileManagerTemp->read($fileName);
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$configPath = __DIR__ . '/../../data/configuration/';
		$configTempPath = __DIR__ . '/../../temp/configuration/';
		$schemaPath = __DIR__ . '/../../data/cfgSchemas/';
		$commandStack = new CommandStack();
		$commandManager = new CommandManager(false, $commandStack);
		$this->fileManager = new JsonFileManager($configPath, $commandManager);
		$this->fileManagerTemp = new JsonFileManager($configTempPath, $commandManager);
		$this->schemaManager = new ComponentSchemaManager($schemaPath, $commandManager);
	}

	/**
	 * Cleanups the test environment
	 */
	protected function tearDown(): void {
		Mockery::close();
	}

}
