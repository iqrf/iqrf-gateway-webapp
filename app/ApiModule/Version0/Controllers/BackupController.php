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
use App\ApiModule\Version0\Models\RestApiSchemaValidator;
use App\ApiModule\Version0\Utils\ContentTypeUtil;
use App\CoreModule\Exceptions\ZipEmptyException;
use App\GatewayModule\Exceptions\InvalidBackupContentException;
use App\GatewayModule\Models\BackupManager;
use App\ServiceModule\Exceptions\UnsupportedInitSystemException;
use JsonException;
use Nette\Utils\FileSystem;

/**
 * Backup controller
 * @Path("/maintenance")
 * @Tag("Backup & Restore")
 */
class BackupController extends BaseController{

	/**
	 * @var BackupManager Backup manager
	 */
	private $manager;

	/**
	 * Constructor
	 * @param BackupManager $manager Backup manager
	 * @param RestApiSchemaValidator $validator REST API JSON schema validator
	 */
	public function __construct(BackupManager $manager, RestApiSchemaValidator $validator) {
		$this->manager = $manager;
		parent::__construct($validator);
	}

	/**
	 * @Path("/backup")
	 * @Method("POST")
	 * @OpenApi("
	 *  summary: Backup gateway
	 *  requestBody:
	 *      required: true
	 *      content:
	 *          application/json:
	 *              schema:
	 *                  $ref: '#/components/schemas/GatewayBackup'
	 *  responses:
	 *      '200':
	 *          description: 'Success'
	 *          content:
	 *              application/zip:
	 *                 schema:
	 *                     type: string
	 *                     format: binary
	 *      '400':
	 *          $ref: '#/components/responses/BadRequest'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function backup(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validator->validateRequest('gatewayBackup', $request);
		try {
			$filePath = $this->manager->backup($request->getJsonBody(true));
			$fileName = basename($filePath);
			$response->writeBody(FileSystem::read($filePath));
			return FileResponseAdjuster::adjust($response, $response->getBody(), $fileName, 'application/zip');
		} catch (ZipEmptyException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S400_BAD_REQUEST, $e);
		}
	}

	/**
	 * @Path("/restore")
	 * @Method("POST")
	 * @OpenApi("
	 *  summary: Restore gateway from backup
	 *  requestBody:
	 *      required: true
	 *      content:
	 *          application/zip:
	 *              schema:
	 *                  type: string
	 *                  format: binary
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/PowerControl'
	 *      '400':
	 *          $ref: '#/components/responses/BadRequest'
	 *      '415':
	 *          description: 'Unsupported media type'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function restore(ApiRequest $request, ApiResponse $response): ApiResponse {
		$contentTypes = ['application/zip', 'application/x-zip-compressed'];
		ContentTypeUtil::validContentType($request, $contentTypes);
		$path = '/tmp/iqrf-gateway-backup-upload.zip';
		FileSystem::write($path, $request->getBody()->getContents());
		try {
			$reboot = $this->manager->restore($path);
			FileSystem::delete($path);
			return $response->writeJsonBody($reboot);
		} catch (InvalidBackupContentException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S400_BAD_REQUEST, $e);
		} catch (JsonException $e) {
			throw new ClientErrorException('Invalid JSON syntax', ApiResponse::S400_BAD_REQUEST, $e);
		} catch (UnsupportedInitSystemException $e) {
			throw new ServerErrorException('Unsupported init system', ApiResponse::S501_NOT_IMPLEMENTED, $e);
		}
	}

}
