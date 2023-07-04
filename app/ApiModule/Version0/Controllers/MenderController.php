<?php

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

namespace App\ApiModule\Version0\Controllers;

use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\OpenApi;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Exception\Api\ClientErrorException;
use Apitte\Core\Exception\Api\ServerErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Models\RestApiSchemaValidator;
use App\ApiModule\Version0\Utils\ContentTypeUtil;
use App\CoreModule\Models\FeatureManager;
use App\MaintenanceModule\Exceptions\MenderFailedException;
use App\MaintenanceModule\Exceptions\MenderInvalidArtifactException;
use App\MaintenanceModule\Exceptions\MenderMissingException;
use App\MaintenanceModule\Exceptions\MenderNoUpdateInProgressException;
use App\MaintenanceModule\Exceptions\MountErrorException;
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
	 * @var FeatureManager $featureManager Feature manager
	 */
	private FeatureManager $featureManager;

	/**
	 * @var MenderManager $manager Mender client configuration manager
	 */
	private MenderManager $manager;

	/**
	 * Constructor
	 * @param FeatureManager $featureManager Feature manager
	 * @param MenderManager $manager Mender client configuration manager
	 * @param RestApiSchemaValidator $validator REST API JSON schema validator
	 */
	public function __construct(FeatureManager $featureManager, MenderManager $manager, RestApiSchemaValidator $validator) {
		$this->featureManager = $featureManager;
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
	 *      '403':
	 *          $ref: '#/components/responses/Forbidden'
	 *      '500':
	 *          $ref: '#/components/responses/ServerError'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function getConfig(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['maintenance:mender']);
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
	 *      '403':
	 *          $ref: '#/components/responses/Forbidden'
	 *      '500':
	 *          $ref: '#/components/responses/ServerError'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function setConfig(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['maintenance:mender']);
		$this->validator->validateRequest('menderConfig', $request);
		try {
			$this->manager->saveConfig($request->getJsonBodyCopy());
			return $response->writeBody('Workaround');
		} catch (IOException | JsonException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	/**
	 * @Path("/config/mender/cert")
	 * @Method("POST")
	 * @OpenApi("
	 *  summary: Uploads and stores a Mender server certificate
	 *  requestBody:
	 *      required: true
	 *      content:
	 *          multipart/form-data:
	 *              schema:
	 *                  type: object
	 *                  properties:
	 *                      certificate:
	 *                          type: string
	 *                          format: binary
	 *
	 *  responses:
	 *      '201':
	 *          description: Created
	 *      '400':
	 *          $ref: '#/components/responses/BadRequest'
	 *      '403':
	 *          $ref: '#/components/responses/Forbidden'
	 *      '500':
	 *          $ref: '#/components/responses/ServerError'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function uploadCert(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['maintenance:mender']);
		try {
			$file = $request->getUploadedFiles()[0];
			$fileName = $file->getClientFilename();
			$content = $file->getStream()->getContents();
			$filePath = $this->manager->saveCertFile($fileName, $content);
			return $response->withStatus(ApiResponse::S201_CREATED)
				->writeBody($filePath);
		} catch (IOException $e) {
			throw new ServerErrorException('Write failure', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
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
	 *      '403':
	 *          $ref: '#/components/responses/Forbidden'
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
		self::checkScopes($request, ['maintenance:mender']);
		ContentTypeUtil::validContentType($request, ['multipart/form-data']);
		try {
			$file = $request->getUploadedFiles()[0];
			$fileName = $file->getClientFilename();
			$this->checkArtifact($fileName);
			$filePath = $this->manager->saveArtifactFile($file);
			return $response->writeBody($this->manager->installArtifact($filePath));
		} catch (MenderInvalidArtifactException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S400_BAD_REQUEST, $e);
		} catch (MenderFailedException | MenderMissingException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		} catch (IOException $e) {
			throw new ServerErrorException('Write failure', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	/**
	 * @Path("/mender/commit")
	 * @Method("POST")
	 * @OpenApi("
	 *  summary: Commits installed mender artifact
	 *  responses:
	 *      '200':
	 *          description: Success
	 *      '400':
	 *          $ref: '#/components/responses/BadRequest'
	 *      '403':
	 *          $ref: '#/components/responses/Forbidden'
	 *      '500':
	 *          $ref: '#/components/responses/ServerError'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function commitUpdate(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['maintenance:mender']);
		try {
			return $response->writeBody($this->manager->commitUpdate());
		} catch (MenderNoUpdateInProgressException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S400_BAD_REQUEST, $e);
		} catch (MenderMissingException | MenderFailedException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	/**
	 * @Path("/mender/rollback")
	 * @Method("POST")
	 * @OpenApi("
	 *  summary: Rolls installed mender artifact back
	 *  responses:
	 *      '200':
	 *          description: Success
	 *      '400':
	 *          $ref: '#/components/responses/BadRequest'
	 *      '403':
	 *          $ref: '#/components/responses/Forbidden'
	 *      '500':
	 *          $ref: '#/components/responses/ServerError'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function rollbackUpdate(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['maintenance:mender']);
		try {
			return $response->writeBody($this->manager->rollbackUpdate());
		} catch (MenderNoUpdateInProgressException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S400_BAD_REQUEST, $e);
		} catch (MenderMissingException | MenderFailedException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	/**
	 * @Path("/mender/remount")
	 * @Method("POST")
	 * @OpenApi("
	 *  summary: Remounts root fs
	 *  requestBody:
	 *      required: true
	 *      content:
	 *          application/json:
	 *              schema:
	 *                  $ref: '#/components/schemas/Remount'
	 *  responses:
	 *      '200':
	 *          description: Success
	 *      '400':
	 *          $ref: '#/components/responses/BadRequest'
	 *      '403':
	 *          $ref: '#/components/responses/Forbidden'
	 *      '500':
	 *          $ref: '#/components/responses/ServerError'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function remount(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['maintenance:mender']);
		if (!$this->featureManager->isEnabled('remount')) {
			throw new ClientErrorException('Remount feature is not enabled.', ApiResponse::S400_BAD_REQUEST);
		}
		$this->validator->validateRequest('remount', $request);
		try {
			$conf = $request->getJsonBodyCopy(true);
			$this->manager->remount($conf['mode']);
			return $response->writeBody('Workaround');
		} catch (MountErrorException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	/**
	 * Checks if uploaded file is a valid mender artifact file
	 * @param string $fileName File name
	 * @throws MenderInvalidArtifactException
	 */
	private function checkArtifact(string $fileName): void {
		if (!str_ends_with($fileName, '.mender')) {
			throw new MenderInvalidArtifactException('Uploaded file is not a .mender artifact file.');
		}
	}

}
