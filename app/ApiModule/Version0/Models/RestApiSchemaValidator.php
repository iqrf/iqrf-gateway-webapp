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

namespace App\ApiModule\Version0\Models;

use Apitte\Core\Exception\Api\ClientErrorException;
use Apitte\Core\Exception\Api\ServerErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\CoreModule\Exceptions\InvalidJsonException;
use App\CoreModule\Exceptions\NonexistentJsonSchemaException;
use App\CoreModule\Models\JsonSchemaManager;
use App\GatewayModule\Models\DaemonDirectories;
use Iqrf\CommandExecutor\CommandExecutor;
use JsonSchema\SchemaStorage;
use Nette\IOException;
use Nette\Utils\FileInfo;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * REST API JSON schema validator
 */
class RestApiSchemaValidator extends JsonSchemaManager {

	/**
	 * Constructor
	 * @param string $directory Directory with files
	 * @param CommandExecutor $commandManager Command managers
	 * @param DaemonDirectories $daemonDirectories IQRF Gateway Daemon directories
	 * @param LoggerInterface $logger Logger
	 */
	public function __construct(
		string $directory,
		CommandExecutor $commandManager,
		private readonly DaemonDirectories $daemonDirectories,
		private readonly LoggerInterface $logger,
	) {
		parent::__construct($directory, $commandManager);
		$this->populateStorage();
	}

	/**
	 * Validates REST API request
	 * @param string $schema REST API JSON schema name
	 * @param ApiRequest $request REST API request
	 * @throws ClientErrorException
	 */
	public function validateRequest(string $schema, ApiRequest $request): void {
		try {
			$this->setSchema($schema);
			$this->validate($request->getJsonBodyCopy(false));
		} catch (JsonException $e) {
			throw new ClientErrorException('Invalid JSON syntax', ApiResponse::S400_BAD_REQUEST, $e);
		} catch (InvalidJsonException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S400_BAD_REQUEST, $e);
		} catch (NonexistentJsonSchemaException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	/**
	 * Validates REST API response
	 * @param string $schema REST API JSON schema name
	 * @param ApiResponse $response REST API request
	 * @return ApiResponse Validated REST API response
	 */
	public function validateResponse(string $schema, ApiResponse $response): ApiResponse {
		try {
			$this->setSchema($schema);
			$json = $response->getJsonBody(false);
			$this->validate($json);
		} catch (JsonException $e) {
			$this->logger->error('Invalid JSON syntax', ['exception' => $e]);
		} catch (InvalidJsonException $e) {
			$this->logger->error('Invalid JSON "' . $schema . '"' . PHP_EOL . $e->getMessage(), ['exception' => $e]);
		} catch (NonexistentJsonSchemaException $e) {
			$this->logger->error('Non-existent JSON schema "' . $schema . '"' . PHP_EOL . $e->getMessage(), ['exception' => $e]);
		}
		$response->rewindBody();
		return $response;
	}

	/**
	 * Populates JSON schema storage
	 */
	private function populateStorage(): void {
		$storage = new SchemaStorage();
		$schemas = [
			[
				'baseUrl' => 'https://apidocs.iqrf.org/iqrf-gateway-daemon-schemas/api/',
				'dir' => $this->daemonDirectories->getApiSchemaDir(),
			],
			[
				'baseUrl' => 'https://apidocs.iqrf.org/iqrf-gateway-daemon-schemas/config/',
				'dir' => $this->daemonDirectories->getConfigurationSchemaDir(),
			],
			[
				'baseUrl' => 'https://apidocs.iqrf.org/openapi/iqrf-gateway-webapp/schemas/',
				'dir' => $this->getBasePath(),
			],
		];
		try {
			foreach ($schemas as $schema) {
				if (!is_dir($schema['dir'])) {
					continue;
				}
				foreach (Finder::findFiles('*.json')->from($schema['dir']) as $file) {
					$this->addSchemaToStorage($storage, $schema['baseUrl'], $file);
				}
			}
			$this->setStorage($storage);
		} catch (Throwable $e) {
			$this->logger->error('Failed to populate JSON schema storage', ['exception' => $e]);
		}
	}

	/**
	 * Adds JSON schema to the storage
	 * @param SchemaStorage $storage JSON schema storage
	 * @param string $baseUrl Base URL
	 * @param FileInfo $fileInfo JSON schema file info
	 */
	private function addSchemaToStorage(SchemaStorage &$storage, string $baseUrl, FileInfo $fileInfo): void {
		try {
			$relativePath = $fileInfo->getRelativePathname();
			$schema = Json::decode(FileSystem::read($fileInfo->getPathname()), forceArrays: true);
			$storage->addSchema($baseUrl . $relativePath, $schema);
		} catch (IOException | JsonException $e) {
			$this->logger->error('Failed to load JSON schema file', [
				'exception' => $e,
				'extra' => [
					'baseUrl' => $baseUrl,
					'relativePath' => $relativePath,
					'path' => $fileInfo->getPath(),
				],
			]);
		}
	}

}
