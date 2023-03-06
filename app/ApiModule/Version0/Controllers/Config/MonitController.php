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

namespace App\ApiModule\Version0\Controllers\Config;

use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\OpenApi;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Exception\Api\ServerErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Controllers\BaseConfigController;
use App\ApiModule\Version0\Models\RestApiSchemaValidator;
use App\MaintenanceModule\Exceptions\MonitConfigErrorException;
use App\MaintenanceModule\Models\MonitManager;
use Nette\IOException;

/**
 * Monit controller
 * @Path("/monit")
 * @Tag("Monit configuration")
 */
class MonitController extends BaseConfigController {

	/**
	 * Constructor
	 * @param MonitManager $manager Monit manager
	 * @param RestApiSchemaValidator $validator REST API JSON schema validator
	 */
	public function __construct(
		private readonly MonitManager $manager,
		RestApiSchemaValidator $validator,
	) {
		parent::__construct($validator);
	}

	/**
	 * @Path("/")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: Returns current monit configuration
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/MonitConfig'
	 *      '403':
	 *          $ref: '#/components/responses/Forbidden'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function get(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['maintenance:monit']);
		try {
			$config = $this->manager->getConfig();
			return $response->writeJsonBody($config);
		} catch (MonitConfigErrorException | IOException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	/**
	 * @Path("/")
	 * @Method("PUT")
	 * @OpenApi("
	 *  summary: Saves updated monit configuration
	 *  requestBody:
	 *      required: true
	 *      content:
	 *          application/json:
	 *              schema:
	 *                  $ref: '#/components/schemas/MonitConfig'
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
	public function save(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['maintenance:monit']);
		$this->validator->validateRequest('monitConfig', $request);
		try {
			$this->manager->saveConfig($request->getJsonBody());
			return $response->writeBody('Workaround');
		} catch (MonitConfigErrorException | IOException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

}
