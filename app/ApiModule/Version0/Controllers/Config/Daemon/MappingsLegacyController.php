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

namespace App\ApiModule\Version0\Controllers\Config\Daemon;

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
 * Mapping controller
 */
#[Path('/mappings')]
#[Tag('Configuration - IQRF Gateway Daemon')]
class MappingsLegacyController extends BaseController {

	/**
	 * Constructor
	 * @param MappingsController $newController New mapping controller
	 * @param ControllerValidators $validators Controller validators
	 */
	public function __construct(
		private readonly MappingsController $newController,
		ControllerValidators $validators,
	) {
		parent::__construct($validators);
	}

	#[Path('/')]
	#[Method('GET')]
	#[OpenApi('
		summary: Lists all mappings
		deprecated: true
		description: "Deprecated in favor of the new IQRF Gateway Daemon mapping controller, use `GET` `/config/daemon/mappings` instead. Will be removed in the version 3.1.0."
		responses:
			\'200\':
				description: Success
				content:
					application/json:
						schema:
							$ref: \'#/components/schemas/MappingList\'
	')]
	#[RequestParameter(name: 'interface', type: 'string', in: 'query', required: false, description: 'Interface type')]
	public function list(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $this->newController->list($request, $response);
	}

	#[Path('/')]
	#[Method('POST')]
	#[OpenApi('
		summary: Creates a new mapping
		deprecated: true
		description: "Deprecated in favor of the new IQRF Gateway Daemon mapping controller, use `POST` `/config/daemon/mappings` instead. Will be removed in the version 3.1.0."
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: \'#/components/schemas/Mapping\'
		responses:
			\'201\':
				description: Created
				content:
					application/json:
						schema:
							$ref: \'#/components/schemas/MappingDetail\'
				headers:
					Location:
						description: Location of information about the created mapping
						schema:
							type: string
			\'400\':
				$ref: \'#/components/responses/BadRequest\'
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
	')]
	public function create(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $this->newController->create($request, $response);
	}

	#[Path('/{id}')]
	#[Method('GET')]
	#[OpenApi('
		summary: Finds mapping by ID
		deprecated: true
		description: "Deprecated in favor of the new IQRF Gateway Daemon mapping controller, use `GET` `/config/daemon/mappings/{id}` instead. Will be removed in the version 3.1.0."
		responses:
			\'200\':
				description: Success
				content:
					application/json:
						schema:
							$ref: \'#/components/schemas/MappingDetail\'
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
			\'404\':
				$ref: \'#/components/responses/NotFound\'
	')]
	#[RequestParameter(name: 'id', type: 'integer', description: 'Mapping ID')]
	public function get(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $this->newController->get($request, $response);
	}

	#[Path('/{id}')]
	#[Method('DELETE')]
	#[OpenApi('
		summary: Removes a mapping
		deprecated: true
		description: "Deprecated in favor of the new IQRF Gateway Daemon mapping controller, use `DELETE` `/config/daemon/mappings/{id}` instead. Will be removed in the version 3.1.0."
		responses:
			\'200\':
				description: Success
			\'404\':
				$ref: \'#/components/responses/NotFound\'
	')]
	#[RequestParameter(name: 'id', type: 'integer', description: 'Mapping ID')]
	public function delete(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $this->newController->delete($request, $response);
	}

	#[Path('/{id}')]
	#[Method('PUT')]
	#[OpenApi('
		summary: Updates a mapping
		deprecated: true
		description: "Deprecated in favor of the new IQRF Gateway Daemon mapping controller, use `PUT` `/config/daemon/mappings/{id}` instead. Will be removed in the version 3.1.0."
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: \'#/components/schemas/Mapping\'
		responses:
			\'200\':
				description: Success
			\'400\':
				$ref: \'#/components/responses/BadRequest\'
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
			\'404\':
				$ref: \'#/components/responses/NotFound\'
	')]
	#[RequestParameter(name: 'id', type: 'integer', description: 'Mapping ID')]
	public function edit(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $this->newController->edit($request, $response);
	}

}
