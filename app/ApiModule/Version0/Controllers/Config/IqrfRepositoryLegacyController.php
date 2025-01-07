<?php

/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Models\ControllerValidators;

/**
 * IQRF Repository controller
 */
#[Path('/iqrf-repository')]
#[Tag('Configuration - IQRF Repository')]
class IqrfRepositoryLegacyController extends BaseConfigController {

	/**
	 * Constructor
	 * @param IqrfRepositoryController $newController New IQRF Repository controller
	 * @param ControllerValidators $validators Controller validators
	 */
	public function __construct(
		private readonly IqrfRepositoryController $newController,
		ControllerValidators $validators,
	) {
		parent::__construct($validators);
	}

	#[Path('/')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Returns the current configuration of IQRF Repository
		deprecated: true
		description: "Deprecated in favor of the new IQRF Repository controller, use `GET` `/config/iqrfRepository` instead. Will be removed in the version 3.1.0."
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/IqrfRepositoryConfig'
			'403':
				$ref: '#/components/responses/Forbidden'
	EOT)]
	public function readConfig(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $this->newController->readConfig($request, $response);
	}

	#[Path('/')]
	#[Method('PUT')]
	#[OpenApi(<<<'EOT'
		summary: Updates IQRF repository extension configuration
		deprecated: true
		description: "Deprecated in favor of the new IQRF Repository controller, use `PUT` `/config/iqrfRepository` instead. Will be removed in the version 3.1.0."
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: '#/components/schemas/IqrfRepositoryConfig'
		responses:
			'200':
				description: Success
			'400':
				$ref: '#/components/responses/BadRequest'
			'403':
				$ref: '#/components/responses/Forbidden'
			'500':
				$ref: '#/components/responses/ServerError'
	EOT)]
	public function saveConfig(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $this->newController->saveConfig($request, $response);
	}

}
