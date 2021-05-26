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

use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\OpenApi;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Exception\Api\ServerErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Models\RestApiSchemaValidator;
use App\MaintenanceModule\Exceptions\MenderFailedException;
use App\MaintenanceModule\Exceptions\MenderMissingException;
use App\MaintenanceModule\Models\MenderManager;
use Nette\IOException;
use Nette\Utils\JsonException;

/**
 * Mender client configuration controller
 * @Path("/")
 * @Tag("Mender")
 */
class MenderController extends BaseController {

	/**
	 * @var MenderManager $manager Mender client configuration manager
	 */
	private $manager;

	/**
	 * Constructor
	 * @param MenderManager $manager Mender client configuration manager
	 * @param RestApiSchemaValidator $validator REST API JSON schema validator
	 */
	public function __construct(MenderManager $manager, RestApiSchemaValidator $validator) {
		$this->manager = $manager;
		parent::__construct($validator);
	}

	/**
	 * @Path("/config/mender")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: Returns current configuration of Mender client
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/MenderConfig'
	 *      '500':
	 *          $ref: '#/components/responses/ServerError'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function getConfig(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			$config = $this->manager->getConfig();
			return $response->writeJsonBody($config);
		} catch (JsonException $e) {
			throw new ServerErrorException('Invalid JSON Syntax', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		} catch (IOException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	/**
	 * @Path("/config/mender")
	 * @Method("PUT")
	 * @OpenApi("
	 *  summary: Saves new Mender client configuration
	 *  requestBody:
	 *      required: true
	 *      content:
	 *          application/json:
	 *              schema:
	 *                  $ref: '#/components/schemas/MenderConfig'
	 *  responses:
	 *      '200':
	 *          description: Success
	 *      '400':
	 *          $ref: '#/components/responses/BadRequest'
	 *      '500':
	 *          $ref: '#/components/responses/ServerError'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function setConfig(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validator->validateRequest('menderConfig', $request);
		try {
			$this->manager->saveConfig($request->getJsonBody());
			return $response->writeBody('Workaround');
		} catch (IOException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	/**
	 * @Path("/mender/install")
	 * @Method("POST")
	 * @OpenApi("
	 *  summary: Installs mender artifact
	 *  requestBody:
	 *      required: true
	 *      content:
	 *          multipart/form-data:
	 *              schema:
	 *                  type: object
	 *                  properties:
	 *                      file:
	 *                          type: string
	 *                          format: binary
	 *
	 *  responses:
	 *      '200':
	 *          description: Success
	 *      '400':
	 *          $ref: '#/components/responses/BadRequest'
	 *      '415':
	 *          description: Unsupported media file
	 *      '500':
	 *          $ref: '#/components/responses/ServerError'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function installArtifact(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			$file = $request->getUploadedFiles()[0];
			$filePath = $this->manager->saveArtifactFile($file->getClientFilename(), $file->getStream()->getContents());
			return $response->writeBody($this->manager->installArtifact($filePath));
		} catch (MenderFailedException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		} catch (MenderMissingException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		} catch (IOException $e) {
			throw new ServerErrorException('Write failure', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

}
