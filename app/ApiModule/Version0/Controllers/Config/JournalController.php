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

namespace App\ApiModule\Version0\Controllers\Config;

use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\OpenApi;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Exception\Api\ServerErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Models\ControllerValidators;
use App\GatewayModule\Exceptions\ConfNotFoundException;
use App\GatewayModule\Exceptions\InvalidConfFormatException;
use App\GatewayModule\Models\JournalConfigManager;

/**
 * Journal controller
 */
#[Path('/journal')]
#[Tag('Configuration - Logs')]
class JournalController extends BaseConfigController {

	/**
	 * Constructor
	 * @param JournalConfigManager $configManager Journal config manager
	 * @param ControllerValidators $validators Controller validators
	 */
	public function __construct(
		private readonly JournalConfigManager $configManager,
		ControllerValidators $validators,
	) {
		parent::__construct($validators);
	}

	#[Path('/')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Returns journal configuration
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/Journal'
			'403':
				$ref: '#/components/responses/Forbidden'
			'500':
				$ref: '#/components/responses/ServerError'
	EOT)]
	public function getConfig(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkFeatures('journal');
		try {
			$response = $response->writeJsonBody($this->configManager->getConfig());
			return $this->validators->validateResponse('journal', $response);
		} catch (ConfNotFoundException | InvalidConfFormatException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	#[Path('/')]
	#[Method('PUT')]
	#[OpenApi(<<<'EOT'
		summary: Updates journal configuration
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: '#/components/schemas/Journal'
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
		$this->validators->checkFeatures('journal');
		$this->validators->validateRequest('journal', $request);
		try {
			$this->configManager->saveConfig($request->getJsonBodyCopy(false));
			return $response;
		} catch (ConfNotFoundException | InvalidConfFormatException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

}
