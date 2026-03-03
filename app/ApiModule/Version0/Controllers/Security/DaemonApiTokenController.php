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
use App\SecurityModule\Exceptions\DaemonApiTokenManagerException;
use App\SecurityModule\Exceptions\DaemonApiTokenNotFoundException;
use App\SecurityModule\Exceptions\DaemonApiTokenNotValidException;
use App\SecurityModule\Models\DaemonApiTokenManager;
use Nette\Utils\JsonException;

/**
 * Daemon API token controller
 */
#[Path('/daemon-access-tokens')]
#[Tag('Security - Daemon API access tokens')]
class DaemonApiTokenController extends BaseSecurityController {

	/**
	 * Constructor
	 * @param DaemonApiTokenManager $manager Daemon API token manager
	 * @param ControllerValidators $validators Controller validators
	 */
	public function __construct(
		private readonly DaemonApiTokenManager $manager,
		ControllerValidators $validators,
	) {
		parent::__construct($validators);
	}

	#[Path('/')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Lists Daemon API access tokens
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/DaemonApiTokenList'
			'403':
				$ref: '#/components/responses/Forbidden'
			'500':
				$ref: '#/components/responses/ServerError'
	EOT)]
	public function listTokens(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['security:daemon-access-tokens']);
		try {
			$response = $response->withHeader('Content-Type', 'application/json')
				->writeBody($this->manager->listTokens());
			return $this->validators->validateResponse('daemonApiTokenList', $response);
		} catch (DaemonApiTokenManagerException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	#[Path('/{id}')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Returns Daemon API access token
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/DaemonApiToken'
			'403':
				$ref: '#/components/responses/Forbidden'
			'404':
				$ref: '#/components/responses/NotFound'
			'500':
				$ref: '#/components/responses/ServerError'
	EOT)]
	#[RequestParameter(name: 'id', type: 'integer', description: 'Daemon API access token ID')]
	public function getToken(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['security:daemon-access-tokens']);
		try {
			$id = (int) $request->getParameter('id');
			$response = $response->withHeader('Content-Type', 'application/json')
				->writeBody($this->manager->getToken($id));
			return $this->validators->validateResponse('daemonApiToken', $response);
		} catch (DaemonApiTokenNotFoundException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S404_NOT_FOUND, $e);
		} catch (DaemonApiTokenManagerException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	#[Path('/')]
	#[Method('POST')]
	#[OpenApi(<<<'EOT'
		summary: Creates a new Daemon API access token
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: '#/components/schemas/DaemonApiTokenCreate'
		responses:
			'201':
				description: Created
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/DaemonApiTokenCreated'
				headers:
					Location:
						description: Location of information about the created Daemon API access token
						schema:
							type: string
			'400':
				$ref: '#/components/responses/BadRequest'
			'403':
				$ref: '#/components/responses/Forbidden'
			'500':
				$ref: '#/components/responses/ServerError'
	EOT)]
	public function createToken(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['security:daemon-access-tokens']);
		$this->validators->validateRequest('daemonApiTokenCreate', $request);
		$data = $request->getJsonBodyCopy(false);
		try {
			$token = $this->manager->createToken($data);
			$response = $response->writeJsonBody($token);
			return $this->validators->validateResponse('daemonApiTokenCreated', $response)
				->withHeader('Location', '/api/v0/security/daemon-access-tokens/' . $token['id'])
				->withStatus(ApiResponse::S201_CREATED);
		} catch (DaemonApiTokenManagerException | JsonException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	#[Path('/{id}/revoke')]
	#[Method('POST')]
	#[OpenApi(<<<'EOT'
		summary: Revokes Daemon API access token
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
	#[RequestParameter(name: 'id', type: 'integer', description: 'Daemon API access token ID')]
	public function revokeToken(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['security:daemon-access-tokens']);
		try {
			$id = (int) $request->getParameter('id');
			$this->manager->revokeToken($id);
			return $response;
		} catch (DaemonApiTokenNotFoundException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S404_NOT_FOUND, $e);
		} catch (DaemonApiTokenManagerException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	#[Path('/{id}/rotate')]
	#[Method('POST')]
	#[OpenApi(<<<'EOT'
		summary: Rotates Daemon API access token
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/DaemonApiTokenCreated'
			'400':
				$ref: '#/components/responses/BadRequest'
			'403':
				$ref: '#/components/responses/Forbidden'
			'404':
				$ref: '#/components/responses/NotFound'
			'500':
				$ref: '#/components/responses/ServerError'
	EOT)]
	#[RequestParameter(name: 'id', type: 'integer', description: 'Daemon API access token ID')]
	public function rotateToken(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['security:daemon-access-tokens']);
		try {
			$id = (int) $request->getParameter('id');
			$token = $this->manager->rotateToken($id);
			$response = $response->writeJsonBody($token);
			return $this->validators->validateResponse('daemonApiTokenCreated', $response);
		} catch (DaemonApiTokenNotValidException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S400_BAD_REQUEST, $e);
		} catch (DaemonApiTokenNotFoundException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S404_NOT_FOUND, $e);
		} catch (DaemonApiTokenManagerException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

}
