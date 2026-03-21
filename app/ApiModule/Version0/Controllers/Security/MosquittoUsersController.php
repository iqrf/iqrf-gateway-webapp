<?php

/**
 * Copyright 2017-2026 IQRF Tech s.r.o.
 * Copyright 2019-2026 MICRORISC s.r.o.
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
use App\SecurityModule\Exceptions\MosquittoPluginManagerException;
use App\SecurityModule\Exceptions\MosquittoPluginManagerInvalidParamsException;
use App\SecurityModule\Exceptions\MosquittoPluginUserNotFoundException;
use App\SecurityModule\Models\MosquittoPluginManager;
use Nette\Utils\JsonException;

/**
 * Mosquitto users API controller
 */
#[Path('/mosquitto-users')]
#[Tag('Security - Mosquitto users API controller')]
class MosquittoUsersController extends BaseSecurityController {

	/**
	 * Constructor
	 * @param MosquittoPluginManager $manager Mosquitto plugin manager
	 * @param ControllerValidators $validators Controller validators
	 */
	public function __construct(
		private readonly MosquittoPluginManager $manager,
		ControllerValidators $validators,
	) {
		parent::__construct($validators);
	}

	#[Path('/')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Lists mosquitto users
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/MosquittoUserList'
			'403':
				$ref: '#/components/responses/Forbidden'
			'500':
				$ref: '#/components/responses/ServerError'
	EOT)]
	public function listUsers(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['security:mosquitto-users']);
		try {
			$response = $response->withHeader('Content-Type', 'application/json')
				->writeBody($this->manager->listUsers());
			return $this->validators->validateResponse('mosquittoUserList', $response);
		} catch (MosquittoPluginManagerException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	#[Path('/{id}')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Returns mosquitto user
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/MosquittoUser'
			'403':
				$ref: '#/components/responses/Forbidden'
			'404':
				$ref: '#/components/responses/NotFound'
			'500':
				$ref: '#/components/responses/ServerError'
	EOT)]
	#[RequestParameter(name: 'id', type: 'integer', description: 'Mosquitto user ID')]
	public function getUser(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['security:mosquitto-users']);
		try {
			$id = (int) $request->getParameter('id');
			$response = $response->withHeader('Content-Type', 'application/json')
				->writeBody($this->manager->getUser($id));
			return $this->validators->validateResponse('mosquittoUser', $response);
		} catch (MosquittoPluginUserNotFoundException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S404_NOT_FOUND, $e);
		} catch (MosquittoPluginManagerException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	#[Path('/')]
	#[Method('POST')]
	#[OpenApi(<<<'EOT'
		summary: Creates a new mosquitto user
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: '#/components/schemas/MosquittoUserCreate'
		responses:
			'201':
				description: Created
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/MosquittoUser'
				headers:
					Location:
						description: Location of information about the created Mosquitto user
						schema:
							type: string
			'400':
				$ref: '#/components/responses/BadRequest'
			'403':
				$ref: '#/components/responses/Forbidden'
			'500':
				$ref: '#/components/responses/ServerError'
	EOT)]
	public function createUser(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['security:mosquitto-users']);
		$this->validators->validateRequest('mosquittoUserCreate', $request);
		$data = $request->getJsonBodyCopy(false);
		try {
			$user = $this->manager->createUser($data);
			$response = $response->writeJsonBody($user);
			return $this->validators->validateResponse('mosquittoUser', $response)
				->withHeader('Location', '/api/v0/security/mosquitto-users/' . $user['id'])
				->withStatus(ApiResponse::S201_CREATED);
		} catch (MosquittoPluginManagerInvalidParamsException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S400_BAD_REQUEST, $e);
		} catch (MosquittoPluginManagerException | JsonException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	#[Path('/{id}/block')]
	#[Method('POST')]
	#[OpenApi(<<<'EOT'
		summary: Blocks mosquitto user
		responses:
			'200':
				description: Success
			'403':
				$ref: '#/components/responses/Forbidden'
			'404':
				$ref: '#/components/responses/NotFound'
			'500':
				$ref: '#/components/responses/ServerError'
	EOT)]
	#[RequestParameter(name: 'id', type: 'integer', description: 'Mosquitto user ID')]
	public function blockUser(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['security:mosquitto-users']);
		try {
			$id = (int) $request->getParameter('id');
			$this->manager->blockUser($id);
			return $response;
		} catch (MosquittoPluginUserNotFoundException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S404_NOT_FOUND, $e);
		} catch (MosquittoPluginManagerException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

}
