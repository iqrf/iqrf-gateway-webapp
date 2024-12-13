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
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Exception\Api\ServerErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Models\ControllerValidators;
use App\GatewayModule\Exceptions\ChpasswdErrorException;
use App\GatewayModule\Models\PasswordManager;

/**
 * Gateway shell user controller
 */
#[Path('/shellUser')]
#[Tag('Security - Shell user password')]
class ShellUserController extends BaseSecurityController {

	/**
	 * Constructor
	 * @param PasswordManager $manager Gateway password manager
	 * @param ControllerValidators $validators Controller validators
	 */
	public function __construct(
		private readonly PasswordManager $manager,
		ControllerValidators $validators,
	) {
		parent::__construct($validators);
	}

	#[Path('/password')]
	#[Method('PUT')]
	#[OpenApi(<<<'EOT'
		summary: Updates default gateway user password
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: '#/components/schemas/GatewayPassword'
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
	public function setPassword(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkFeatures(['gatewayPass']);
		$this->validators->validateRequest('gatewayPassword', $request);
		try {
			$this->manager->setPassword($request->getJsonBodyCopy()['password']);
			return $response;
		} catch (ChpasswdErrorException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

}
