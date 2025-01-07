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

namespace App\ApiModule\Version0\Controllers\Security;

use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\OpenApi;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Annotation\Controller\RequestParameter;
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Controllers\Gateway\BaseGatewayController;
use App\ApiModule\Version0\Models\ControllerValidators;

/**
 * Gateway SSH controller
 */
#[Path('/ssh')]
#[Tag('Security - SSH keys')]
class SshController extends BaseGatewayController {

	/**
	 * Constructor
	 * @param SshKeysController $newController SSH keys controller
	 * @param ControllerValidators $validators Controller validators
	 */
	public function __construct(
		private readonly SshKeysController $newController,
		ControllerValidators $validators,
	) {
		parent::__construct($validators);
	}

	#[Path('/keyTypes')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Lists SSH key types
		deprecated: true
		description: "Deprecated in favor of the new SSH keys controller, use `GET` `/security/sshKeys/types` instead. Will be removed in the version 3.1.0."
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/SshKeyTypes'
			'403':
				$ref: '#/components/responses/Forbidden'
			'500':
				$ref: '#/components/responses/ServerError'
	EOT)]
	public function listKeyTypes(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $this->newController->listKeyTypes($request, $response);
	}

	#[Path('/keys')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: List authorized SSH public keys
		deprecated: true
		description: "Deprecated in favor of the new SSH keys controller, use `GET` `/security/sshKeys` instead. Will be removed in the version 3.1.0."
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/SshKeyList'
			'403':
				$ref: '#/components/responses/Forbidden'
			'500':
				$ref: '#/components/responses/ServerError'
	EOT)]
	public function listKeys(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $this->newController->listKeys($request, $response);
	}

	#[Path('/keys/{id}')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Returns authorized SSH public key
		deprecated: true
		description: "Deprecated in favor of the new SSH keys controller, use `GET` `/security/sshKeys/{id}` instead. Will be removed in the version 3.1.0."
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/SshKeyDetail'
			'403':
				$ref: '#/components/responses/Forbidden'
			'404':
				$ref: '#/components/responses/NotFound'
			'500':
				$ref: '#/components/responses/ServerError'
	EOT)]
	#[RequestParameter(name: 'id', type: 'integer', description: 'SSH public key ID')]
	public function getKey(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $this->newController->getKey($request, $response);
	}

	#[Path('/keys')]
	#[Method('POST')]
	#[OpenApi(<<<'EOT'
		summary: Adds SSH keys for key-based authentication
		deprecated: true
		description: "Deprecated in favor of the new SSH keys controller, use `POST` `/security/sshKeys` instead. Will be removed in the version 3.1.0."
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: '#/components/schemas/SshKeysAdd'
		responses:
			'200':
				description: 'Partial success, duplicate keys in body (ignored)'
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/SshKeyCreated'
			'201':
				description: Created
			'400':
				$ref: '#/components/responses/BadRequest'
			'403':
				$ref: '#/components/responses/Forbidden'
			'409':
				description: SSH public key already exists
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/Error'
			'500':
				$ref: '#/components/responses/ServerError'
	EOT)]
	public function addKeys(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $this->newController->addKeys($request, $response);
	}

	#[Path('/keys/{id}')]
	#[Method('DELETE')]
	#[OpenApi(<<<'EOT'
		summary: Removes an authorized SSH public key
		deprecated: true
		description: "Deprecated in favor of the new SSH keys controller, use `DELETE` `/security/sshKeys/{id}` instead. Will be removed in the version 3.1.0."
		responses:
			'200':
				description: Success
			'403':
				$ref: '#/components/responses/Forbidden'
			'404':
				$ref: '#/components/responses/NotFound'
	EOT)]
	#[RequestParameter(name: 'id', type: 'integer', description: 'SSH public key ID')]
	public function deleteKey(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $this->newController->deleteKey($request, $response);
	}

}
