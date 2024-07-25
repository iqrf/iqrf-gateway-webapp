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
use App\GatewayModule\Exceptions\TuptimeErrorException;
use App\GatewayModule\Exceptions\TuptimeNotFoundException;
use App\GatewayModule\Models\PowerManager;
use App\GatewayModule\Models\TuptimeManager;

/**
 * Gateway power controller
 */
#[Path('/power')]
#[Tag('Gateway - Power management')]
class PowerController extends BaseGatewayController {

	/**
	 * Constructor
	 * @param PowerManager $powerManager Gateway power manager
	 * @param TuptimeManager $tuptimeManager Gateway uptime stats manager
	 * @param ControllerValidators $validators Controller validators
	 */
	public function __construct(
		private readonly PowerManager $powerManager,
		private readonly TuptimeManager $tuptimeManager,
		ControllerValidators $validators,
	) {
		parent::__construct($validators);
	}

	#[Path('/poweroff')]
	#[Method('POST')]
	#[OpenApi('
		summary: Powers off the gateway
		responses:
			\'200\':
				description: Success
				content:
					application/json:
						schema:
							$ref: \'#/components/schemas/PowerControl\'
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
	')]
	public function powerOff(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['gateway:power']);
		$response = $response->writeJsonBody($this->powerManager->powerOff());
		return $this->validators->validateResponse('powerControl', $response);
	}

	#[Path('/reboot')]
	#[Method('POST')]
	#[OpenApi('
		summary: Reboots the gateway
		responses:
			\'200\':
				description: Success
				content:
					application/json:
						schema:
							$ref: \'#/components/schemas/PowerControl\'
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
	')]
	public function reboot(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['gateway:power']);
		$response = $response->writeJsonBody($this->powerManager->reboot());
		return $this->validators->validateResponse('powerControl', $response);
	}

	#[Path('/stats')]
	#[Method('GET')]
	#[OpenApi('
		summary: Returns power statistics
		responses:
			\'200\':
				description: Success
				content:
					application/json:
						schema:
							$ref: \'#/components/schemas/TuptimeStats\'
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
			\'424\':
				description: tuptime not found
				content:
					\'application/json\':
						schema:
							$ref: \'#/components/schemas/Error\'
			\'500\':
				$ref: \'#/components/responses/ServerError\'
	')]
	public function stats(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['gateway:power']);
		try {
			$response = $response->writeJsonBody($this->tuptimeManager->list());
			return $this->validators->validateResponse('tuptimeStats', $response);
		} catch (TuptimeNotFoundException $e) {
			throw new ClientErrorException('tuptime not found', ApiResponse::S424_FAILED_DEPENDENCY, $e);
		} catch (TuptimeErrorException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

}
