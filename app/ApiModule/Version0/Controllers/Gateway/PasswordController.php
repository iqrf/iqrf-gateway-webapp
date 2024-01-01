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

namespace App\ApiModule\Version0\Controllers\Gateway;

use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\OpenApi;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Exception\Api\ClientErrorException;
use Apitte\Core\Exception\Api\ServerErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Controllers\GatewayController;
use App\ApiModule\Version0\Models\RestApiSchemaValidator;
use App\CoreModule\Models\FeatureManager;
use App\GatewayModule\Exceptions\ChpasswdErrorException;
use App\GatewayModule\Models\PasswordManager;

/**
 * Gateway password controller
 */
#[Path('/password')]
class PasswordController extends GatewayController {

	/**
	 * Constructor
	 * @param FeatureManager $featureManager Feature manager
	 * @param PasswordManager $manager Gateway password manager
	 * @param RestApiSchemaValidator $validator REST API JSON schema validator
	 */
	public function __construct(
		private readonly FeatureManager $featureManager,
		private readonly PasswordManager $manager,
		RestApiSchemaValidator $validator,
	) {
		parent::__construct($validator);
	}

	#[Path('/')]
	#[Method('PUT')]
	#[OpenApi('
		summary: Sets default gateway user password
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: \'#/components/schemas/GatewayPassword\'
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
	public function setPassword(ApiRequest $request, ApiResponse $response): ApiResponse {
		if (!$this->featureManager->isEnabled('gatewayPass')) {
			throw new ClientErrorException('Gateway password feature is not enabled', ApiResponse::S400_BAD_REQUEST);
		}
		$this->validator->validateRequest('gatewayPassword', $request);
		try {
			$this->manager->setPassword($request->getJsonBodyCopy(true)['password']);
			return $response->writeBody('Workaround');
		} catch (ChpasswdErrorException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

}
