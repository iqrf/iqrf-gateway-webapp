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

namespace App\ApiModule\Version0\Controllers\Gateway;

use Apitte\Core\Adjuster\FileResponseAdjuster;
use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\OpenApi;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Exception\Api\ClientErrorException;
use Apitte\Core\Exception\Api\ServerErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Controllers\GatewayController;
use App\ApiModule\Version0\Models\RestApiSchemaValidator;
use App\GatewayModule\Models\BackupManager;
use Nette\Utils\FileSystem;

/**
 * Backup controller
 * @Path("/")
 * @Tag("Gateway backup")
 */
class BackupController extends GatewayController {

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
	 *  summary: Gateway backup
	 *  requestBody:
	 *      required: true
	 *      content:
	 *          application/json:
	 *              schema:
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
	public function backup(ApiRequest $request, ApiResponse $response) {
		$this->validator->validateRequest('gatewayBackup', $request);
		$filePath = $this->manager->backup($request->getJsonBody(true));
		$fileName = basename($filePath);
		$response->writeBody(FileSystem::read($filePath));
		return FileResponseAdjuster::adjust($response, $response->getBody(), $fileName, 'application/zip');
	}

}
