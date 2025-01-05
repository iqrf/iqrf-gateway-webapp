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
use App\CoreModule\Models\CommandManager;
use App\CoreModule\Models\JsonSchemaManager;
use App\GatewayModule\Models\DaemonDirectories;
use JsonSchema\SchemaStorage;
use Nette\IOException;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Nette\Utils\Strings;
use Psr\Log\LoggerInterface;
use SplFileInfo;
use Throwable;

/**
 * REST API JSON schema validator
 */
class RestApiSchemaValidator extends JsonSchemaManager {

	/**
	 * @var DaemonDirectories IQRF Gateway Daemon directories
	 */
	private DaemonDirectories $daemonDirectories;

	/**
	 * @var LoggerInterface Logger
	 */
	private LoggerInterface $logger;

	/**
	 * Constructor
	 * @param string $directory Directory with files
	 * @param CommandManager $commandManager Command managers
	 * @param LoggerInterface $logger Logger
	 */
	public function __construct(
		string $directory,
		CommandManager $commandManager,
		DaemonDirectories $daemonDirectories,
		LoggerInterface $logger
	) {
		parent::__construct($directory, $commandManager);
		$this->daemonDirectories = $daemonDirectories;
		$this->logger = $logger;
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
				'baseUrl' => 'https://apidocs.iqrf.org/iqrf-gateway-webapp-api/schemas/',
				'dir' => $this->getBasePath(),
			],
		];
		try {
			foreach ($schemas as $schema) {
				if (!is_dir($schema['dir'])) {
					continue;
				}
				foreach (Finder::findFiles('*.json')->from($schema['dir']) as $file) {
					$this->addSchemaToStorage($storage, $schema['baseUrl'], $schema['dir'], $file);
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
	 * @param string $dir Directory path
	 * @param SplFileInfo $fileInfo JSON schema file info
	 */
	private function addSchemaToStorage(SchemaStorage &$storage, string $baseUrl, string $dir, SplFileInfo $fileInfo): void {
		try {
			$relativePath = $this->getRelativePathname($fileInfo, $dir);
			$schema = Json::decode(FileSystem::read($fileInfo->getPathname()), Json::FORCE_ARRAY);
			$storage->addSchema($baseUrl . $relativePath, $schema);
		} catch (IOException | JsonException $e) {
			$this->logger->error('Failed to load JSON schema file', ['exception' => $e, 'extra' => ['baseUrl' => $baseUrl, 'relativePath' => $relativePath, 'path' => $fileInfo->getPath()]]);
		}
	}

	/**
	 * Returns relative pathname of the JSON schema file
	 * @param SplFileInfo $fileInfo File info
	 * @param string $dirPath Directory path
	 * @return string Relative pathname
	 */
	private function getRelativePathname(SplFileInfo $fileInfo, string $dirPath): string {
		$relativePath = $fileInfo->getPathname();
		if (Strings::startsWith($relativePath, $dirPath)) {
			$relativePath = Strings::substring($relativePath, Strings::length($dirPath));
		}
		return ltrim($relativePath, '/');
	}

}
