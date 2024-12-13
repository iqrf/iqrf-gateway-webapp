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

namespace App\ApiModule\Version0\Controllers\Network;

use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\OpenApi;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Annotation\Controller\RequestParameter;
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Models\ControllerValidators;

/**
 * Network operator controller
 */
#[Path('/operators')]
#[Tag('IP network - Cellular operators')]
class OperatorController extends BaseNetworkController {

	/**
	 * Constructor
	 * @param CellularOperatorsController $newController New Cellular operators controller
	 * @param ControllerValidators $validators Controller validators
	 */
	public function __construct(
		private readonly CellularOperatorsController $newController,
		ControllerValidators $validators,
	) {
		parent::__construct($validators);
	}

	#[Path('/')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Lists all network operators
		deprecated: true
		description: "Deprecated in favor of the new Cellular operators controller, use `GET` `/network/cellular/operators` instead. Will be removed in the version 3.1.0."
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/NetworkOperatorList'
			'403':
				$ref: '#/components/responses/Forbidden'
	EOT)]
	public function list(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $this->newController->list($request, $response);
	}

	#[Path('/{id}')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Returns a network operator configuration
		deprecated: true
		description: "Deprecated in favor of the new Cellular operators controller, use `GET` `/network/cellular/operators/{id}` instead. Will be removed in the version 3.1.0."
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/NetworkOperator'
			'403':
				$ref: '#/components/responses/Forbidden'
			'404':
				$ref: '#/components/responses/NotFound'
	EOT)]
	#[RequestParameter(name: 'id', type: 'integer', description: 'Operator ID')]
	public function get(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $this->newController->get($request, $response);
	}

	#[Path('/')]
	#[Method('POST')]
	#[OpenApi(<<<'EOT'
		summary: Creates a new network operator
		deprecated: true
		description: "Deprecated in favor of the new Cellular operators controller, use `POST` `/network/cellular/operators` instead. Will be removed in the version 3.1.0."
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: '#/components/schemas/NetworkOperator'
		responses:
			'201':
				description: Created
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/NetworkOperator'
				headers:
					Location:
						description: Location of information about network operator
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
	#[Method('PUT')]
	#[OpenApi(<<<'EOT'
		summary: Edits a network operator
		deprecated: true
		description: "Deprecated in favor of the new Cellular operators controller, use `PUT` `/network/cellular/operators/{id}` instead. Will be removed in the version 3.1.0."
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: '#/components/schemas/NetworkOperator'
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
	#[RequestParameter(name: 'id', type: 'integer', description: 'Operator ID')]
	public function edit(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $this->newController->edit($request, $response);
	}

	#[Path('/{id}')]
	#[Method('DELETE')]
	#[OpenApi(<<<'EOT'
		summary: Removes a network operator
		deprecated: true
		description: "Deprecated in favor of the new Cellular operators controller, use `DELETE` `/network/cellular/operators/{id}` instead. Will be removed in the version 3.1.0."
		responses:
			'200':
				description: Success
			'403':
				$ref: '#/components/responses/Forbidden'
			'404':
				$ref: '#/components/responses/NotFound'
	EOT)]
	#[RequestParameter(name: 'id', type: 'integer', description: 'Operator ID')]
	public function delete(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $this->newController->delete($request, $response);
	}

}
