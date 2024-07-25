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
use App\MaintenanceModule\Exceptions\MonitConfigErrorException;
use App\MaintenanceModule\Models\MonitManager;
use Nette\IOException;

/**
 * Monit controller
 */
#[Path('/monit')]
#[Tag('Configuration - Monit')]
class MonitController extends BaseConfigController {

	/**
	 * Constructor
	 * @param MonitManager $manager Monit manager
	 * @param ControllerValidators $validators Controller validators
	 */
	public function __construct(
		private readonly MonitManager $manager,
		ControllerValidators $validators,
	) {
		parent::__construct($validators);
	}

	#[Path('/')]
	#[Method('GET')]
	#[OpenApi('
		summary: Returns the current Monit configuration
		responses:
			\'200\':
				description: Success
				content:
					application/json:
						schema:
							$ref: \'#/components/schemas/MonitConfig\'
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
			\'500\':
				$ref: \'#/components/responses/ServerError\'
	 ')]
	public function get(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['maintenance:monit']);
		try {
			$config = $this->manager->getConfig();
			$response = $response->writeJsonBody($config);
			return $this->validators->validateResponse('monitConfig', $response);
		} catch (MonitConfigErrorException | IOException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	#[Path('/')]
	#[Method('PUT')]
	#[OpenApi('
		summary: Updates the Monit configuration
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: \'#/components/schemas/MonitConfig\'
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
	public function save(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['maintenance:monit']);
		$this->validators->validateRequest('monitConfig', $request);
		try {
			$this->manager->saveConfig($request->getJsonBodyCopy());
			return $response;
		} catch (MonitConfigErrorException | IOException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	#[Path('/checks/{name}')]
	#[Method('GET')]
	#[OpenApi('
		summary: Returns the Monit check configuration
		responses:
			\'200\':
				description: Success
				content:
					application/json:
						schema:
							$ref: \'#/components/schemas/MonitCheckConfig\'
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
			\'404\':
				$ref: \'#/components/responses/NotFound\'
	')]
	#[RequestParameter(name: 'name', type: 'string', in: 'path', description: 'Check name')]
	public function getCheck(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['maintenance:monit']);
		try {
			$config = $this->manager->getCheck($request->getParameter('name'));
			$response = $response->writeJsonBody($config);
			return $this->validators->validateResponse('monitCheckConfig', $response);
		} catch (IOException $e) {
			throw new ClientErrorException('Not found', ApiResponse::S404_NOT_FOUND, $e);
		}
	}

	#[Path('/checks/{name}/enable')]
	#[Method('POST')]
	#[OpenApi('
		summary: Enables Monit check
		responses:
			\'200\':
				description: Success
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
			\'404\':
				$ref: \'#/components/responses/NotFound\'
	')]
	#[RequestParameter(name: 'name', type: 'string', in: 'path', description: 'Check name')]
	public function enableCheck(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['maintenance:monit']);
		try {
			$this->manager->enableCheck($request->getParameter('name'));
			return $response;
		} catch (IOException $e) {
			throw new ClientErrorException('Not found', ApiResponse::S404_NOT_FOUND, $e);
		}
	}

	#[Path('/checks/{name}/disable')]
	#[Method('POST')]
	#[OpenApi('
		summary: Disables Monit check
		responses:
			\'200\':
				description: Success
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
			\'404\':
				$ref: \'#/components/responses/NotFound\'
	')]
	#[RequestParameter(name: 'name', type: 'string', in: 'path', description: 'Check name')]
	public function disableCheck(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['maintenance:monit']);
		try {
			$this->manager->disableCheck($request->getParameter('name'));
			return $response;
		} catch (IOException $e) {
			throw new ClientErrorException('Not found', ApiResponse::S404_NOT_FOUND, $e);
		}
	}

}
