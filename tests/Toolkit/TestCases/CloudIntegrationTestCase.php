<?php

/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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
use App\ConfigModule\Models\GenericManager;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Iqrf\CommandExecutor\CommandExecutor;
use Iqrf\CommandExecutor\CommandStack;
use Iqrf\FileManager\FileManager;
use Mockery;
use Tester\TestCase;

/**
 * Cloud service integration test case
 */
abstract class CloudIntegrationTestCase extends TestCase {

	/**
	 * @var string Path to a directory with certificates and private keys
	 */
	protected string $certPath;

	/**
	 * @var GenericManager Generic configuration manager
	 */
	protected GenericManager $configManager;

	/**
	 * @var FileManager JSON file manager
	 */
	protected FileManager $fileManager;

	/**
	 * Sets up the test environment
	 */
	public function __construct() {
		$this->certPath = realpath(TMP_DIR . '/certificates/') . '/';
		$configPath = TMP_DIR . '/configuration/';
		$schemaPath = TESTER_DIR . '/data/cfgSchemas/';
		$commandStack = new CommandStack();
		$commandManager = new CommandExecutor(false, $commandStack);
		$this->fileManager = new FileManager($configPath, $commandManager);
		$schemaManager = new ComponentSchemaManager($schemaPath, $commandManager);
		$this->configManager = new GenericManager($this->fileManager, $schemaManager);
	}

	/**
	 * Mocks the HTTP(S) client
	 * @param string $content File's content
	 * @return Client HTTP(S) client
	 */
	protected function mockHttpClient(string $content): Client {
		$response = new MockHandler([
			new Response(200, [], $content),
		]);
		$handler = HandlerStack::create($response);
		return new Client(['handler' => $handler]);
	}

	/**
	 * Cleanups the test environment
	 */
	protected function tearDown(): void {
		Mockery::close();
	}

}
