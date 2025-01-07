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

namespace App\ApiModule\Version0\Controllers;

use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\OpenApi;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Annotation\Controller\RequestParameter;
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Models\ControllerValidators;

/**
 * User manager API controller
 */
#[Path('/user')]
#[Tag('Account')]
class UserController extends BaseController {

	/**
	 * Constructor
	 * @param AccountController $newAccountController New account controller
	 * @param ControllerValidators $validators Controller validators
	 */
	public function __construct(
		private readonly AccountController $newAccountController,
		ControllerValidators $validators,
	) {
		parent::__construct($validators);
	}

	#[Path('/')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Returns information about logged in user
		deprecated: true
		description: "Deprecated in favor of the new account controller, use `GET` `/account` instead. Will be removed in the version 3.1.0."
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/UserDetail'
			'403':
				$ref: '#/components/responses/ForbiddenApiKey'
	EOT)]
	public function get(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $this->newAccountController->get($request, $response);
	}

	#[Path('/')]
	#[Method('PUT')]
	#[OpenApi(<<<'EOT'
		summary: Updates the user account information
		deprecated: true
		description: "Deprecated in favor of the new account controller, use `PUT` `/account` instead. Will be removed in the version 3.1.0."
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: '#/components/schemas/UserEdit'
		responses:
			'200':
				description: Success
			'400':
				$ref: '#/components/responses/BadRequest'
			'403':
				$ref: '#/components/responses/ForbiddenApiKey'
			'409':
				description: Username or e-mail address is already used
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/Error'
	EOT)]
	public function edit(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $this->newAccountController->edit($request, $response);
	}

	#[Path('/password')]
	#[Method('PUT')]
	#[OpenApi(<<<'EOT'
		summary: "Updates user's password"
		deprecated: true
		description: "Deprecated in favor of the new account controller, use `PUT` `/account/password` instead. Will be removed in the version 3.1.0."
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: '#/components/schemas/PasswordChange'
		responses:
			'200':
				description: Success
			'403':
				$ref: '#/components/responses/ForbiddenApiKey'
	EOT)]
	public function changePassword(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $this->newAccountController->changePassword($request, $response);
	}

	#[Path('/password/recovery')]
	#[Method('POST')]
	#[OpenApi(<<<'EOT'
		summary: Requests the password recovery
		deprecated: true
		description: "Deprecated in favor of the new account controller, use `POST` `/account/passwordRecovery` instead. Will be removed in the version 3.1.0."
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: '#/components/schemas/PasswordRecoveryRequest'
		responses:
			'200':
				description: Success
			'403':
				description: E-mail address is not verified
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/Error'
			'404':
				description: User not found
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/Error'
			'500':
				$ref: '#/components/responses/MailerError'
	EOT)]
	public function requestPasswordRecovery(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $this->newAccountController->requestPasswordRecovery($request, $response);
	}

	#[Path('/password/recovery/{uuid}')]
	#[Method('POST')]
	#[OpenApi(<<<'EOT'
		summary: Recovers the forgotten password
		deprecated: true
		description: "Deprecated in favor of the new account controller, use `POST` `/account/passwordRecovery/{uuid}` instead. Will be removed in the version 3.1.0."
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: '#/components/schemas/PasswordRecovery'
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/UserToken'
			'404':
				$ref: '#/components/responses/NotFound'
			'410':
				description: Password recovery request is expired
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/Error'
	EOT)]
	#[RequestParameter(name: 'uuid', type: 'string', description: 'Password recovery request UUID')]
	public function recoverPassword(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $this->newAccountController->recoverPassword($request, $response);
	}

	#[Path('/resendVerification')]
	#[Method('POST')]
	#[OpenApi(<<<'EOT'
		summary: Resends the verification e-mail
		deprecated: true
		description: "Deprecated in favor of the new account controller, use `POST` `/account/emailVerification/resend` instead. Will be removed in the version 3.1.0."
		responses:
			'200':
				description: Success
			'400':
				description: User is already verified
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/Error'
			'500':
				$ref: '#/components/responses/MailerError'
	EOT)]
	public function resendVerification(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $this->newAccountController->resendVerification($request, $response);
	}

	#[Path('/refreshToken')]
	#[Method('POST')]
	#[OpenApi(<<<'EOT'
		summary: Refreshes user access token
		deprecated: true
		description: "Deprecated in favor of the new account controller, use `POST` `/account/tokenRefresh` instead. Will be removed in the version 3.1.0."
		responses:
			'201':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/UserToken'
			'403':
				$ref: '#/components/responses/ForbiddenApiKey'
	EOT)]
	public function refreshToken(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $this->newAccountController->refreshToken($request, $response);
	}

	#[Path('/signIn')]
	#[Method('POST')]
	#[OpenApi(<<<'EOT'
		summary: Signs in the user
		deprecated: true
		description: "Deprecated in favor of the new account controller, use `POST` `/account/signIn` instead. Will be removed in the version 3.1.0."
		security:
			- []
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: '#/components/schemas/UserSignIn'
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/UserToken'
			'400':
				$ref: '#/components/responses/BadRequest'
			'500':
				$ref: '#/components/responses/ServerError'
	EOT)]
	public function signIn(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $this->newAccountController->signIn($request, $response);
	}

	#[Path('/verify/{uuid}')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Verifies the user
		deprecated: true
		description: "Deprecated in favor of the new account controller, use `GET` `/account/emailVerification/{uuid}` instead. Will be removed in the version 3.1.0."
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/UserToken'
			'404':
				$ref: '#/components/responses/NotFound'
	EOT)]
	#[RequestParameter(name: 'uuid', type: 'string', description: 'User verification UUID')]
	public function verify(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $this->newAccountController->verify($request, $response);
	}

}
