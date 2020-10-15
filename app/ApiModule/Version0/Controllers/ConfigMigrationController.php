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

namespace App\ApiModule\Version0\Controllers;

use Apitte\Core\Adjuster\FileResponseAdjuster;
use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\OpenApi;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Exception\Api\ClientErrorException;
use Apitte\Core\Exception\Api\ServerErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Utils\ContentTypeUtil;
use App\ConfigModule\Exceptions\IncompleteConfigurationException;
use App\ConfigModule\Models\MigrationManager;
use App\ServiceModule\Exceptions\UnsupportedInitSystemException;
use Nette\Utils\FileSystem;
use Nette\Utils\JsonException;

/**
 * Configuration migration controller
 * @Path("/daemon/migration")
 * @Tag("Config manager")
 */
class ConfigMigrationController extends BaseConfigController {

	/**
	 * @var MigrationManager Configuration migration manager
	 */
	private $manager;

	/**
	 * Constructor
	 * @param MigrationManager $manager Configuration migration manager
	 */
	public function __construct(MigrationManager $manager) {
		$this->manager = $manager;
	}

	/**
	 * @Path("/export")
	 * @Method("GET")
	 * @OpenApi("
	 *   summary: Exports the configuration
	 *   responses:
	 *     '200':
	 *       description: 'Success'
	 *       content:
	 *         application/zip:
	 *           schema:
	 *             type: string
	 *             format: binary
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function export(ApiRequest $request, ApiResponse $response): ApiResponse {
		$path = $this->manager->createArchive();
		$fileName = basename($path);
		$response->writeBody(FileSystem::read($path));
		return FileResponseAdjuster::adjust($response, $response->getBody(), $fileName, 'application/zip');
	}

	/**
	 * @Path("/import")
	 * @Method("POST")
	 * @OpenApi("
	 *  summary: Imports the configuration
	 *  requestBody:
	 *      required: true
	 *      content:
	 *          application/zip:
	 *              schema:
	 *                  type: string
	 *                  format: binary
	 *  responses:
	 *      '200':
	 *          description: 'Success'
	 *      '400':
	 *          $ref: '#/components/responses/BadRequest'
	 *      '415':
	 *          description: 'Unsupported media type'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function import(ApiRequest $request, ApiResponse $response): ApiResponse {
		$contentTypes = ['application/zip', 'application/x-zip-compressed'];
		ContentTypeUtil::validContentType($request, $contentTypes);
		$path = '/tmp/iqrf-gateway-configuration-upload.zip';
		FileSystem::write($path, $request->getBody()->getContents());
		try {
			$this->manager->extractArchive($path);
		} catch (JsonException $e) {
			throw new ClientErrorException('Invalid JSON syntax', ApiResponse::S400_BAD_REQUEST);
		} catch (IncompleteConfigurationException $e) {
			throw new ClientErrorException('Incomplete configuration', ApiResponse::S400_BAD_REQUEST);
		} catch (UnsupportedInitSystemException $e) {
			throw new ServerErrorException('Unsupported init system', ApiResponse::S501_NOT_IMPLEMENTED);
		}
		FileSystem::delete($path);
		return $response->writeBody('Workaround');
	}

}
