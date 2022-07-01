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

use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\OpenApi;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Exception\Api\ClientErrorException;
use Apitte\Core\Exception\Api\ServerErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Controllers\GatewayController;
use App\ApiModule\Version0\Models\RestApiSchemaValidator;
use App\GatewayModule\Exceptions\NonexistentTimezoneException;
use App\GatewayModule\Exceptions\TimeDateException;
use App\GatewayModule\Models\TimeManager;

/**
 * Time controller
 * @Path("/time")
 */
class TimeController extends GatewayController {

	/**
	 * @var TimeManager Time manager
	 */
	private TimeManager $manager;

	/**
	 * Constructor
	 * @param TimeManager $manager Time manager
	 * @param RestApiSchemaValidator $validator REST API JSON schema validator
	 */
	public function __construct(TimeManager $manager, RestApiSchemaValidator $validator) {
		$this->manager = $manager;
		parent::__construct($validator);
	}

	/**
	 * @Path("/")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: 'Returns current gateway date, time and timezone'
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/TimeGet'
	 *      '500':
	 *         $ref: '#/components/responses/ServerError'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function getTime(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			$time = $this->manager->currentTime();
			return $response->writeJsonBody($time);
		} catch (TimeDateException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	/**
	 * @Path("/timezones")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: Returns available timezones
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/TimezoneList'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function getTimezones(ApiRequest $request, ApiResponse $response): ApiResponse {
		$timezones = $this->manager->availableTimezones();
		return $response->writeJsonBody($timezones);
	}

	/**
	 * @Path("/timezone")
	 * @Method("PUT")
	 * @OpenApi("
	 *  summary: Sets new gateway timezone
	 *  requestBody:
	 *      required: true
	 *      content:
	 *          application/json:
	 *              schema:
	 *                 $ref: '#/components/schemas/TimezoneSet'
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/TimezoneSet'
	 *      '400':
	 *          description: Not found
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function setTimezone(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validator->validateRequest('timezoneSet', $request);
		try {
			$body = $request->getJsonBody();
			$this->manager->setTimezone($body['timezone']);
			return $response->writeBody('Workaround');
		} catch (NonexistentTimezoneException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S404_NOT_FOUND, $e);
		}
	}

}
