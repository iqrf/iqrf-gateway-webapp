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
use Apitte\Core\Annotation\Controller\RequestParameter;
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Exception\Api\ClientErrorException;
use Apitte\Core\Exception\Api\ServerErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Models\ControllerValidators;
use App\ConfigModule\Exceptions\ControllerPinConfigNotFoundException;
use App\ConfigModule\Models\ControllerConfigManager;
use App\ConfigModule\Models\ControllerPinConfigManager;
use Nette\IOException;
use Nette\Utils\JsonException;

/**
 * IQRF Gateway Controller configuration controller
 */
#[Path('/controller')]
#[Tag('Configuration - IQRF Gateway Controller')]
class ControllerController extends BaseConfigController {

	/**
	 * Constructor
	 * @param ControllerConfigManager $configManager IQRF Gateway Controller configuration manager
	 * @param ControllerPinConfigManager $pinManager IQRF Gateway Controller pin configuration manager
	 * @param ControllerValidators $validators Controller validators
	 */
	public function __construct(
		private readonly ControllerConfigManager $configManager,
		private readonly ControllerPinConfigManager $pinManager,
		ControllerValidators $validators,
	) {
		parent::__construct($validators);
	}

	#[Path('/')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Returns the current configuration of IQRF Gateway Controller
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/ControllerConfig'
			'403':
				$ref: '#/components/responses/Forbidden'
			'500':
				$ref: '#/components/responses/ServerError'
	EOT)]
	public function getConfig(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['config:controller']);
		try {
			$config = $this->configManager->getConfig();
			$response = $response->writeJsonBody($config);
			return $this->validators->validateResponse('controllerConfig', $response);
		} catch (JsonException $e) {
			throw new ServerErrorException('Invalid JSON syntax', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		} catch (IOException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	#[Path('/')]
	#[Method('PUT')]
	#[OpenApi(<<<'EOT'
		summary: Updates the configuration of IQRF Gateway Controller
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: '#/components/schemas/ControllerConfig'
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
	public function setConfig(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['config:controller']);
		$this->validators->validateRequest('controllerConfig', $request);
		try {
			$this->configManager->saveConfig($request->getJsonBodyCopy());
			return $response;
		} catch (IOException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	#[Path('/pins')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Lists all pin configurations
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/ControllerPinConfigList'
			'403':
				$ref: '#/components/responses/Forbidden'
	EOT)]
	public function listPins(ApiRequest $request, ApiResponse $response): ApiResponse {
		$response = $response->writeJsonBody($this->pinManager->listPinConfigs());
		return $this->validators->validateResponse('controllerPinConfigList', $response);
	}

	#[Path('/pins/{id}')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Returns the pin configuration profile
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/ControllerPinConfig'
			'403':
				$ref: '#/components/responses/Forbidden'
			'404':
				$ref: '#/components/responses/NotFound'
	EOT)]
	#[RequestParameter(name: 'id', type: 'integer', description: 'Controller pin configuration profile ID')]
	public function getPins(ApiRequest $request, ApiResponse $response): ApiResponse {
		$id = (int) $request->getParameter('id');
		try {
			$entity = $this->pinManager->getPinConfig($id);
			$response = $response->writeJsonObject($entity);
			return $this->validators->validateResponse('controllerPinConfig', $response);
		} catch (ControllerPinConfigNotFoundException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S404_NOT_FOUND, $e);
		}
	}

	#[Path('/pins')]
	#[Method('POST')]
	#[OpenApi(<<<'EOT'
		summary: Creates a new pin configuration profile
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: '#/components/schemas/ControllerPinConfig'
		responses:
			'201':
				description: Created
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/ControllerPinConfig'
				headers:
					Location:
						description: Location of information about created pin configuration profile
						schema:
							type: string
			'400':
				$ref: '#/components/responses/BadRequest'
			'403':
				$ref: '#/components/responses/Forbidden'
	EOT)]
	public function addPins(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->validateRequest('controllerPinConfig', $request);
		$json = $request->getJsonBodyCopy(false);
		$entity = $this->pinManager->addPinConfig($json);
		$response = $response->writeJsonObject($entity)
			->withHeader('Location', '/api/v0/config/controller/pins/' . $entity->getId())
			->withStatus(ApiResponse::S201_CREATED);
		return $this->validators->validateResponse('controllerPinConfig', $response);
	}

	#[Path('/pins/{id}')]
	#[Method('PUT')]
	#[OpenApi(<<<'EOT'
		summary: Updates the pin configuration profile
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: '#/components/schemas/ControllerPinConfig'
		responses:
			'200':
				description: Success
			'400':
				$ref: '#/components/responses/BadRequest'
			'403':
				$ref: '#/components/responses/Forbidden'
			'404':
				$ref: '#/components/responses/NotFound'
			'500':
				$ref: '#/components/responses/ServerError'
	EOT)]
	#[RequestParameter(name: 'id', type: 'integer', description: 'Controller pin configuration profile ID')]
	public function editPins(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->validateRequest('controllerPinConfig', $request);
		$id = (int) $request->getParameter('id');
		$json = $request->getJsonBodyCopy(false);
		try {
			$this->pinManager->editPinConfig($id, $json);
			return $response;
		} catch (ControllerPinConfigNotFoundException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S404_NOT_FOUND, $e);
		}
	}

	#[Path('/pins/{id}')]
	#[Method('DELETE')]
	#[OpenApi(<<<'EOT'
		summary: Deletes the pin configuration profile
		responses:
			'200':
				description: Success
			'403':
				$ref: '#/components/responses/Forbidden'
			'404':
				$ref: '#/components/responses/NotFound'
	EOT)]
	#[RequestParameter(name: 'id', type: 'integer', description: 'Controller pin configuration profile ID')]
	public function removePins(ApiRequest $request, ApiResponse $response): ApiResponse {
		$id = (int) $request->getParameter('id');
		try {
			$this->pinManager->removePinConfig($id);
			return $response;
		} catch (ControllerPinConfigNotFoundException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S404_NOT_FOUND, $e);
		}
	}

}
