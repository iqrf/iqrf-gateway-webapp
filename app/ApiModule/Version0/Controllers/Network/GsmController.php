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

namespace App\ApiModule\Version0\Controllers\Network;

use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\OpenApi;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Exception\Api\ServerErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Controllers\NetworkController;
use App\ApiModule\Version0\Models\RestApiSchemaValidator;
use App\NetworkModule\Exceptions\ModemManagerException;
use App\NetworkModule\Models\GsmManager;

/**
 * GSM controller
 * @Path("/gsm")
 */
class GsmController extends NetworkController {

	/**
	 * Constructor
	 * @param GsmManager $gsmManager GSM manager
	 * @param RestApiSchemaValidator $validator REST API JSON schema validator
	 */
	public function __construct(
		private readonly GsmManager $gsmManager,
		RestApiSchemaValidator $validator,
	) {
		parent::__construct($validator);
	}

	/**
	 * @Path("/modems")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: Lists available modems
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/ModemList'
	 *      '403':
	 *          $ref: '#/components/responses/Forbidden'
	 *      '500':
	 *          $ref: '#/components/responses/ServerError'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function listModems(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['network']);
		try {
			return $response->writeJsonBody($this->gsmManager->listModems());
		} catch (ModemManagerException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	/**
	 * @Path("/modems/scan")
	 * @Method("POST")
	 * @OpenApi("
	 *  summary: Scans for GSM modems
	 *  responses:
	 *      '200':
	 *          description: Success
	 *      '403':
	 *          $ref: '#/components/responses/Forbidden'
	 *      '500':
	 *          $ref: '#/components/responses/ServerError'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function scanModems(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['network']);
		try {
			$this->gsmManager->scanModems();
			return $response->writeBody('Workaround');
		} catch (ModemManagerException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

}
