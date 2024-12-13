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

namespace App\ApiModule\Version0\Controllers\Security;

use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\OpenApi;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Annotation\Controller\RequestParameter;
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Controllers\BaseController;
use App\ApiModule\Version0\Models\ControllerValidators;

/**
 * API keys controller
 */
#[Path('/apiKeys')]
#[Tag('Security - API key management')]
class ApiKeyLegacyController extends BaseController {

	/**
	 * Constructor
	 * @param ApiKeyController $newController New API keys controller
	 * @param ControllerValidators $validators Controller validators
	 */
	public function __construct(
		private readonly ApiKeyController $newController,
		ControllerValidators $validators,
	) {
		parent::__construct($validators);
	}

	#[Path('/')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Lists all API keys
		description: "Deprecated in favor of the new API keys controller, use `GET` `/security/apiKeys` instead. Will be removed in the version 3.1.0."
		deprecated: true
		responses:
			200:
				description: Success
				content:
					application/json:
						schema:
							type: array
							items:
								$ref: '#/components/schemas/ApiKeyDetail'
			403:
				$ref: '#/components/responses/Forbidden'
	EOT)]
	public function list(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $this->newController->list($request, $response);
	}

	#[Path('/')]
	#[Method('POST')]
	#[OpenApi(<<<'EOT'
		summary: Creates a new API key
		deprecated: true
		description: "Deprecated in favor of the new API keys controller, use `POST` `/security/apiKeys` instead. Will be removed in the version 3.1.0."
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: '#/components/schemas/ApiKeyModify'
		responses:
			'201':
				description: Created
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/ApiKeyCreated'
				headers:
					Location:
						description: Location of information about the created API key
						schema:
							type: string
			'400':
				$ref: '#/components/responses/BadRequest'
			'403':
				$ref: '#/components/responses/Forbidden'
	EOT)]
	public function create(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $this->newController->create($request, $response);
	}

	#[Path('/{id}')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Returns API key by ID
		deprecated: true
		description: "Deprecated in favor of the new API keys controller, use `GET` `/security/apiKeys/{id}` instead. Will be removed in the version 3.1.0."
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/ApiKeyDetail'
			'404':
				$ref: '#/components/responses/NotFound'
			'403':
				$ref: '#/components/responses/Forbidden'
	EOT)]
	#[RequestParameter('id', type: 'integer', description: 'API key ID')]
	public function get(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $this->newController->get($request, $response);
	}

	#[Path('/{id}')]
	#[Method('DELETE')]
	#[OpenApi(<<<'EOT'
		summary: Deletes a API key
		deprecated: true
		description: "Deprecated in favor of the new API keys controller, use `DELETE` `/security/apiKeys/{id}` instead. Will be removed in the version 3.1.0."
		responses:
			'200':
				description: Success
			'403':
				$ref: '#/components/responses/Forbidden'
			'404':
				$ref: '#/components/responses/NotFound'
	EOT)]
	#[RequestParameter('id', type: 'integer', description: 'API key ID')]
	public function delete(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $this->newController->delete($request, $response);
	}

	#[Path('/{id}')]
	#[Method('PUT')]
	#[OpenApi(<<<'EOT'
		summary: Updates the API key
		deprecated: true
		description: "Deprecated in favor of the new API keys controller, use `PUT` `/security/apiKeys/{id}` instead. Will be removed in the version 3.1.0."
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: '#/components/schemas/ApiKeyModify'
		responses:
			'200':
				description: Success
			'400':
				$ref: '#/components/responses/BadRequest'
			'403':
				$ref: '#/components/responses/Forbidden'
			'404':
				$ref: '#/components/responses/NotFound'
	EOT)]
	#[RequestParameter(name: 'id', type: 'integer', description: 'API key ID')]
	public function update(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $this->newController->update($request, $response);
	}

}
