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
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Controllers\GatewayController;
use App\ApiModule\Version0\Models\RestApiSchemaValidator;
use App\GatewayModule\Models\PowerManager;

/**
 * Gateway power controller
 */
#[Path('/power')]
class PowerController extends GatewayController {

	/**
	 * Constructor
	 * @param PowerManager $powerManager Gateway power manager
	 * @param RestApiSchemaValidator $validator REST API JSON schema validator
	 */
	public function __construct(
		private readonly PowerManager $powerManager,
		RestApiSchemaValidator $validator,
	) {
		parent::__construct($validator);
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
		self::checkScopes($request, ['gateway:power']);
		return $response->writeJsonBody($this->powerManager->powerOff());
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
		self::checkScopes($request, ['gateway:power']);
		return $response->writeJsonBody($this->powerManager->reboot());
	}

}
