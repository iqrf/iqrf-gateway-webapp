<?php

/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
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
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Exception\Api\ClientErrorException;
use Apitte\Core\Exception\Api\ServerErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Models\ControllerValidators;
use App\GatewayModule\Exceptions\NonexistentTimezoneException;
use App\GatewayModule\Exceptions\TimeDateException;
use App\GatewayModule\Models\TimeManager;

/**
 * Time controller
 */
#[Path('/time')]
#[Tag('Gateway - Date & time')]
class TimeController extends BaseGatewayController {

	/**
	 * Constructor
	 * @param TimeManager $manager Time manager
	 * @param ControllerValidators $validators Controller validators
	 */
	public function __construct(
		private readonly TimeManager $manager,
		ControllerValidators $validators,
	) {
		parent::__construct($validators);
	}

	#[Path('/')]
	#[Method('GET')]
	#[OpenApi('
		summary: \'Returns current gateway date, time, timezone and NTP configuration\'
		responses:
			\'200\':
				description: Success
				content:
					application/json:
						schema:
							$ref: \'#/components/schemas/TimeGet\'
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
			\'500\':
				$ref: \'#/components/responses/ServerError\'
	')]
	public function getTime(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			$time = $this->manager->getTime();
			$response = $response->writeJsonBody($time);
			return $this->validators->validateResponse('timeGet', $response);
		} catch (TimeDateException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR);
		}
	}

	#[Path('/')]
	#[Method('POST')]
	#[OpenApi('
		summary: \'Sets date, time, timezone and NTP configuration\'
		responses:
			\'200\':
				description: Success
			\'400\':
				$ref: \'#/components/responses/BadRequest\'
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
			\'500\':
				$ref: \'#/components/responses/ServerError\'
	')]
	public function setTime(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->validateRequest('timeSet', $request);
		try {
			$time = $request->getJsonBodyCopy(true);
			$this->manager->setTime($time);
			return $response;
		} catch (NonexistentTimezoneException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S400_BAD_REQUEST);
		}
	}

	#[Path('/timezones')]
	#[Method('GET')]
	#[OpenApi('
		summary: Returns available timezones
		responses:
			\'200\':
				description: Success
				content:
					application/json:
						schema:
							$ref: \'#/components/schemas/TimezoneList\'
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
	')]
	public function getTimezones(ApiRequest $request, ApiResponse $response): ApiResponse {
		$timezones = $this->manager->availableTimezones();
		$response = $response->writeJsonBody($timezones);
		return $this->validators->validateResponse('timezoneList', $response);
	}

}
