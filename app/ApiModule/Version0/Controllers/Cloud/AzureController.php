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

namespace App\ApiModule\Version0\Controllers\Cloud;

use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\OpenApi;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Exception\Api\ClientErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Models\ControllerValidators;
use App\CloudModule\Exceptions\InvalidConnectionStringException;
use App\CloudModule\Models\AzureManager;

/**
 * Microsoft Azure IoT Hub connection controller
 */
#[Path('/azure')]
class AzureController extends BaseCloudController {

	/**
	 * Constructor
	 * @param AzureManager $manager Microsoft Azure IoT Hub connection manager
	 * @param ControllerValidators $validators Controller validators
	 */
	public function __construct(
		AzureManager $manager,
		ControllerValidators $validators,
	) {
		parent::__construct($validators);
		$this->manager = $manager;
	}

	#[Path('/')]
	#[Method('POST')]
	#[OpenApi(<<<'EOT'
		summary: Creates a new MQTT connection into Microsoft Azure IoT Hub
		requestBody:
			description: Microsoft Azure IoT Hub connection configuration
			required: true
			content:
				application/json:
					schema:
						$ref: '#/components/schemas/CloudAzure'
		responses:
			'201':
				description: Created
			'400':
				$ref: '#/components/responses/BadRequest'
			'403':
				$ref: '#/components/responses/Forbidden'
			'500':
				$ref: '#/components/responses/ServerError'
	EOT)]
	public function create(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->checkRequest('cloudAzure', $request);
		try {
			return parent::create($request, $response);
		} catch (InvalidConnectionStringException $e) {
			throw new ClientErrorException('Invalid connection string', ApiResponse::S400_BAD_REQUEST, $e);
		}
	}

}
