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
 * User manager API controller
 */
#[Path('/users')]
#[Tag('Security - User management')]
class UsersLegacyController extends BaseController {

	/**
	 * Constructor
	 * @param UsersController $newController New user manager controller
	 * @param ControllerValidators $validators Controller validators
	 */
	public function __construct(
		private readonly UsersController $newController,
		ControllerValidators $validators,
	) {
		parent::__construct($validators);
	}

	#[Path('/')]
	#[Method('GET')]
	#[OpenApi('
		summary: Lists all users
		deprecated: true
		responses:
			\'200\':
				description: Success
				content:
					application/json:
						schema:
							type: array
							items:
								$ref: \'#/components/schemas/UserDetail\'
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
	')]
	public function list(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $this->newController->list($request, $response);
	}

	#[Path('/')]
	#[Method('POST')]
	#[OpenApi('
		summary: Creates a new user
		deprecated: true
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: \'#/components/schemas/UserCreate\'
		responses:
			\'201\':
				description: Created
				headers:
					Location:
						description: Location of information about the created user
						schema:
							type: string
			\'400\':
				$ref: \'#/components/responses/BadRequest\'
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
			\'409\':
				description: E-mail address or username is already used
				content:
					application/json:
						schema:
							$ref: \'#/components/schemas/Error\'
	')]
	public function create(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $this->newController->create($request, $response);
	}

	#[Path('/{id}')]
	#[Method('GET')]
	#[OpenApi('
		summary: Returns user by ID
		deprecated: true
		responses:
			\'200\':
				description: Success
				content:
					application/json:
						schema:
							$ref: \'#/components/schemas/UserDetail\'
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
			\'404\':
				$ref: \'#/components/responses/NotFound\'
	')]
	#[RequestParameter(name: 'id', type: 'integer', description: 'User ID')]
	public function get(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $this->newController->get($request, $response);
	}

	#[Path('/{id}')]
	#[Method('DELETE')]
	#[OpenApi('
		summary: Deletes the user
		deprecated: true
		responses:
			\'200\':
				description: Success
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
			\'404\':
				$ref: \'#/components/responses/NotFound\'
	')]
	#[RequestParameter(name: 'id', type: 'integer', description: 'User ID')]
	public function delete(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $this->newController->delete($request, $response);
	}

	#[Path('/{id}')]
	#[Method('PUT')]
	#[OpenApi('
		summary: Updates the user
		deprecated: true
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: \'#/components/schemas/UserEdit\'
		responses:
			\'200\':
				description: Success
			\'400\':
				$ref: \'#/components/responses/BadRequest\'
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
			\'404\':
				$ref: \'#/components/responses/NotFound\'
			\'409\':
				description: Username is already used
				content:
					application/json:
						schema:
							$ref: \'#/components/schemas/Error\'
	')]
	#[RequestParameter(name: 'id', type: 'integer', description: 'User ID')]
	public function update(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $this->newController->edit($request, $response);
	}

	#[Path('/{id}/resendVerification')]
	#[Method('POST')]
	#[OpenApi('
		summary: Resends the verification e-mail
		deprecated: true
		responses:
			\'200\':
				description: Success
			\'400\':
				description: User is already verified
				content:
					application/json:
						schema:
							$ref: \'#/components/schemas/Error\'
			\'404\':
				$ref: \'#/components/responses/NotFound\'
			\'500\':
				$ref: \'#/components/responses/MailerError\'
	')]
	#[RequestParameter(name: 'id', type: 'integer', description: 'User ID')]
	public function resendVerification(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $this->newController->resendVerification($request, $response);
	}

}
