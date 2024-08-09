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
use Apitte\Core\Exception\Api\ClientErrorException;
use Apitte\Core\Exception\Api\ServerErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Models\ControllerValidators;
use App\GatewayModule\Exceptions\SshDirectoryException;
use App\GatewayModule\Exceptions\SshInvalidKeyException;
use App\GatewayModule\Exceptions\SshKeyExistsException;
use App\GatewayModule\Exceptions\SshKeyNotFoundException;
use App\GatewayModule\Exceptions\SshUtilityException;
use App\GatewayModule\Models\SshManager;
use Nette\IOException;

/**
 * SSH keys controller
 */
#[Path('/sshKeys')]
#[Tag('Security - SSH keys')]
class SshKeysController extends BaseSecurityController {

	/**
	 * Constructor
	 * @param SshManager $manager SSH manager
	 * @param ControllerValidators $validators Controller validators
	 */
	public function __construct(
		private readonly SshManager $manager,
		ControllerValidators $validators,
	) {
		parent::__construct($validators);
	}

	#[Path('/types')]
	#[Method('GET')]
	#[OpenApi('
		summary: Lists SSH key types
		responses:
			\'200\':
				description: Success
				content:
					application/json:
						schema:
							$ref: \'#/components/schemas/SshKeyTypes\'
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
			\'500\':
				$ref: \'#/components/responses/ServerError\'
	')]
	public function listKeyTypes(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['sshKeys']);
		try {
			$response = $response->writeJsonBody($this->manager->listKeyTypes());
			return $this->validators->validateResponse('sshKeyTypes', $response);
		} catch (SshUtilityException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	#[Path('/')]
	#[Method('GET')]
	#[OpenApi('
		summary: List authorized SSH public keys
		responses:
			\'200\':
				description: Success
				content:
					application/json:
						schema:
							$ref: \'#/components/schemas/SshKeyList\'
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
			\'500\':
				$ref: \'#/components/responses/ServerError\'
	')]
	public function listKeys(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['sshKeys']);
		$response = $response->writeJsonBody($this->manager->listKeys());
		return $this->validators->validateResponse('sshKeyList', $response);
	}

	#[Path('/{id}')]
	#[Method('GET')]
	#[OpenApi('
		summary: Returns authorized SSH public key
		responses:
			\'200\':
				description: Success
				content:
					application/json:
						schema:
							$ref: \'#/components/schemas/SshKeyDetail\'
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
			\'404\':
				$ref: \'#/components/responses/NotFound\'
			\'500\':
				$ref: \'#/components/responses/ServerError\'
	')]
	#[RequestParameter(name: 'id', type: 'integer', description: 'SSH public key ID')]
	public function getKey(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['sshKeys']);
		try {
			$id = (int) $request->getParameter('id');
			$response = $response->writeJsonObject($this->manager->getKey($id));
			return $this->validators->validateResponse('sshKeyDetail', $response);
		} catch (SshKeyNotFoundException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S404_NOT_FOUND, $e);
		}
	}

	#[Path('/')]
	#[Method('POST')]
	#[OpenApi('
		summary: Adds SSH keys for key-based authentication
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: \'#/components/schemas/SshKeysAdd\'
		responses:
			\'200\':
				description: \'Partial success, duplicate keys in body (ignored)\'
				content:
					application/json:
						schema:
							$ref: \'#/components/schemas/SshKeyCreated\'
			\'201\':
				description: Created
			\'400\':
				$ref: \'#/components/responses/BadRequest\'
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
			\'409\':
				description: SSH public key already exists
				content:
					application/json:
						schema:
							$ref: \'#/components/schemas/Error\'
			\'500\':
				$ref: \'#/components/responses/ServerError\'
	')]
	public function addKeys(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['sshKeys']);
		$this->validators->validateRequest('sshKeysAdd', $request);
		try {
			$failed = $this->manager->addKeys($request->getJsonBodyCopy(true));
			if ($failed !== []) {
				$response = $response->withStatus(ApiResponse::S200_OK)
					->writeJsonBody(['failedKeys' => $failed]);
				return $this->validators->validateResponse('sshKeyCreated', $response);
			}
			return $response->withStatus(ApiResponse::S201_CREATED);
		} catch (SshInvalidKeyException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S400_BAD_REQUEST, $e);
		} catch (SshKeyExistsException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S409_CONFLICT, $e);
		} catch (IOException | SshDirectoryException | SshUtilityException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	#[Path('/{id}')]
	#[Method('DELETE')]
	#[OpenApi('
		summary: Removes an authorized SSH public key
		responses:
			\'200\':
				description: Success
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
			\'404\':
				$ref: \'#/components/responses/NotFound\'
	')]
	#[RequestParameter(name: 'id', type: 'integer', description: 'SSH public key ID')]
	public function deleteKey(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['sshKeys']);
		try {
			$id = (int) $request->getParameter('id');
			$this->manager->deleteKey($id);
			return $response;
		} catch (SshKeyNotFoundException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S404_NOT_FOUND, $e);
		}
	}

}
