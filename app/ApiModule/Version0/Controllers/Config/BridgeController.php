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
use App\ConfigModule\Models\IqrfConfigManager;
use Nette\IOException;
use Nette\Utils\JsonException;

/**
 * IQRF Gateway InfluxDB Bridge configuration controller
 */
#[Path('/bridge')]
#[Tag('IQRF Gateway InfluxDB Bridge configuration')]
class BridgeController extends BaseConfigController {

	/**
	 * Constructor
	 * @param IqrfConfigManager $configManager IQRF software configuration manager
	 * @param RestApiSchemaValidator $validator REST API JSON schema validator
	 */
	public function __construct(
		private readonly IqrfConfigManager $configManager,
		RestApiSchemaValidator $validator,
	)
	{
		parent::__construct($validator);
	}

	#[Path('/')]
	#[Method('GET')]
	#[OpenApi('
		summary: Returns current configuration of IQRF Gateway InfluxDB Bridge
		responses:
			\'200\':
				description: Success
				content:
					application/json:
						schema:
							$ref: \'#/components/schemas/BridgeConfig\'
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
			\'500\':
				$ref: \'#/components/responses/ServerError\'
	')]
	public function getConfig(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['config:bridge']);
		try {
			$config = $this->configManager->getConfig();
			return $response->writeJsonBody($config);
		} catch (JsonException $e) {
			throw new ServerErrorException('Invalid JSON syntax', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		} catch (IOException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	#[Path('/')]
	#[Method('PUT')]
	#[OpenApi('
		summary: Saves new configuration of IQRF Gateway InfluxDB Bridge
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: \'#/components/schemas/BridgeConfig\'
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
	public function setConfig(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['config:bridge']);
		$this->validator->validateRequest('bridgeConfig', $request);
		try {
			$this->configManager->saveConfig($request->getJsonBodyCopy());
			return $response->writeBody('Workaround');
		} catch (IOException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

}
