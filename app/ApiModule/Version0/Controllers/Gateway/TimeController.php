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

namespace App\ApiModule\Version0\Controllers\Gateway;

use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\OpenApi;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Controllers\GatewayController;
use App\ApiModule\Version0\Models\RestApiSchemaValidator;
use App\GatewayModule\Models\TimeManager;

/**
 * Time controller
 * @Path("/time")
 */
class TimeController extends GatewayController {

	/**
	 * @var TimeManager Time manager
	 */
	private $manager;

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
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function getTime(ApiRequest $request, ApiResponse $response): ApiResponse {
		$time = $this->manager->currentTime();
		return $response->writeJsonBody($time);
	}

	/**
	 * @Path("/")
	 * @Method("PUT")
	 * @OpenApi("
	 *  summary: Sets new time from timestamp
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/TimeSet'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function setTime(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validator->validateRequest('timeSet', $request);
		$body = $request->getJsonBody();
		if ($body['sync']) {
			$this->manager->setTime($body['sync']);
		} else {
			$this->manager->setTime($body['sync'], $body['timestamp']);
		}
		return $response->writeBody('Workaround');
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
		return $response->writeJsonBody(['timezones' => $timezones]);
	}

	/**
	 * @Path("/timezone")
	 * @Method("PUT")
	 * @OpenApi("
	 *  summary: Sets new gateway timezone
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/TimezoneSet'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function setTimezone(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validator->validateRequest('timezoneSet', $request);
		$body = $request->getJsonBody();
		$this->manager->setTimezone($body['timezone']);
		return $response->writeBody('Workaround');
	}

}
