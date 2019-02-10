<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2019 IQRF Tech s.r.o.
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

use App\CloudModule\Models\IManager;
use App\ConfigModule\Models\GenericManager;
use App\CoreModule\Models\JsonFileManager;
use App\CoreModule\Models\JsonSchemaManager;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Mockery\Mock;
use Tester\TestCase;

/**
 * Cloud service integration test case
 */
abstract class CloudIntegrationTestCase extends TestCase {

	/**
	 * @var string Path to a directory with certificates and private keys
	 */
	protected $certPath;

	/**
	 * @var GenericManager Generic configuration manager
	 */
	protected $configManager;

	/**
	 * @var JsonFileManager JSON file manager
	 */
	protected $fileManager;

	/**
	 * @var Mock|IManager Cloud manager
	 */
	protected $manager;

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
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$this->certPath = realpath(__DIR__ . '/../../temp/certificates/') . '/';
		$configPath = __DIR__ . '/../../temp/configuration/';
		$schemaPath = __DIR__ . '/../../temp/cfgSchemas/';
		$this->fileManager = new JsonFileManager($configPath);
		$schemaManager = new JsonSchemaManager($schemaPath);
		$this->configManager = new GenericManager($this->fileManager, $schemaManager);
	}

	/**
	 * Cleanups the test environment
	 */
	protected function tearDown(): void {
		Mockery::close();
	}

}
