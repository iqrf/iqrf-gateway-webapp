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
use App\ApiModule\Version0\RequestAttributes;
use App\CoreModule\Models\UserManager;
use App\GatewayModule\Exceptions\NonexistentTimezoneException;
use App\GatewayModule\Exceptions\TimeDateException;
use App\GatewayModule\Models\TimeManager;
use App\Models\Database\Entities\User;

/**
 * Time controller
 * @Path("/time")
 */
class TimeController extends GatewayController {

	/**
	 * @var TimeManager Time manager
	 */
	private TimeManager $timeManager;

	/**
	 * @var UserManager User manager
	 */
	private UserManager $userManager;

	/**
	 * Constructor
	 * @param TimeManager $timeManager Time manager
	 * @param UserManager $userManager User manager
	 * @param RestApiSchemaValidator $validator REST API JSON schema validator
	 */
	public function __construct(TimeManager $timeManager, UserManager $userManager, RestApiSchemaValidator $validator) {
		$this->timeManager = $timeManager;
		$this->userManager = $userManager;
		parent::__construct($validator);
	}

	/**
	 * @Path("/")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: 'Returns current gateway date, time, timezone and ntp configuration'
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
			$time = $this->timeManager->getTime();
			return $response->writeJsonBody($time);
		} catch (TimeDateException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR);
		}
	}

	/**
	 * @Path("/")
	 * @Method("POST")
	 * @OpenApi("
	 *  summary: 'Sets date, time, timezone and ntp configuration'
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/UserToken'
	 *      '400':
	 *          $ref: '#/components/responses/BadRequest'
	 *      '500':
	 *          $ref: '#/components/responses/ServerError'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function setTime(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validator->validateRequest('timeSet', $request);
		$user = $request->getAttribute(RequestAttributes::APP_LOGGED_USER);
		try {
			$time = $request->getJsonBody(true);
			$this->timeManager->setTime($time);
			if ($user instanceof User) {
				$json = $user->jsonSerialize();
				$json['token'] = $this->userManager->generateToken($user);
				return $response->writeJsonBody($json);
			}
			return $response->writeBody('Workaround');
		} catch (NonexistentTimezoneException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S400_BAD_REQUEST);
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
		$timezones = $this->timeManager->availableTimezones();
		return $response->writeJsonBody($timezones);
	}

}
