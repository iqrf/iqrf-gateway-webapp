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
use Apitte\Core\Exception\Api\ClientErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Models\ControllerValidators;
use App\Exceptions\ApiKeyExpirationPassedException;
use App\Exceptions\ApiKeyInvalidExpirationException;
use App\Models\Database\Entities\ApiKey;
use App\Models\Database\EntityManager;
use App\Models\Database\Repositories\ApiKeyRepository;

/**
 * API keys controller
 */
#[Path('/apiKeys')]
#[Tag('Security - API key management')]
class ApiKeyController extends BaseSecurityController {

	/**
	 * @var ApiKeyRepository API key database repository
	 */
	private readonly ApiKeyRepository $repository;

	/**
	 * Constructor
	 * @param EntityManager $entityManager Entity manager
	 * @param ControllerValidators $validators Controller validators
	 */
	public function __construct(
		private readonly EntityManager $entityManager,
		ControllerValidators $validators,
	) {
		parent::__construct($validators);
		$this->repository = $entityManager->getApiKeyRepository();
	}

	#[Path('/')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Lists all API keys
		responses:
			200:
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/ApiKeyList'
			403:
				$ref: '#/components/responses/Forbidden'
	EOT)]
	public function list(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['apiKeys']);
		$apiKeys = $this->repository->findAll();
		$response = $response->writeJsonBody($apiKeys);
		return $this->validators->validateResponse('apiKeyList', $response);
	}

	#[Path('/')]
	#[Method('POST')]
	#[OpenApi(<<<'EOT'
		summary: Creates a new API key
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: '#/components/schemas/ApiKeyModify'
		responses:
			'201':
				description: Created
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/ApiKeyCreated'
				headers:
					Location:
						description: Location of information about the created API key
						schema:
							type: string
			'400':
				$ref: '#/components/responses/BadRequest'
			'403':
				$ref: '#/components/responses/Forbidden'
	EOT)]
	public function create(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['apiKeys']);
		$this->validators->validateRequest('apiKeyModify', $request);
		$json = $request->getJsonBodyCopy(false);
		$apiKey = new ApiKey($json->description, null);
		try {
			$apiKey->setExpirationFromString($json->expiration);
		} catch (ApiKeyExpirationPassedException | ApiKeyInvalidExpirationException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S400_BAD_REQUEST, $e);
		}
		$this->entityManager->persist($apiKey);
		$this->entityManager->flush();
		$response = $response->writeJsonObject($apiKey)
			->withHeader('Location', '/api/v0/apiKeys/' . $apiKey->getId())
			->withStatus(ApiResponse::S201_CREATED);
		return $this->validators->validateResponse('apiKeyCreated', $response);
	}

	#[Path('/{id}')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Returns API key by ID
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/ApiKeyDetail'
			'404':
				$ref: '#/components/responses/NotFound'
			'403':
				$ref: '#/components/responses/Forbidden'
	EOT)]
	#[RequestParameter('id', type: 'integer', description: 'API key ID')]
	public function get(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['apiKeys']);
		$id = (int) $request->getParameter('id');
		$apiKey = $this->repository->find($id);
		if ($apiKey === null) {
			throw new ClientErrorException('API key not found', ApiResponse::S404_NOT_FOUND);
		}
		$response = $response->writeJsonObject($apiKey);
		return $this->validators->validateResponse('apiKeyDetail', $response);
	}

	#[Path('/{id}')]
	#[Method('DELETE')]
	#[OpenApi(<<<'EOT'
		summary: Deletes a API key
		responses:
			'200':
				description: Success
			'403':
				$ref: '#/components/responses/Forbidden'
			'404':
				$ref: '#/components/responses/NotFound'
	EOT)]
	#[RequestParameter('id', type: 'integer', description: 'API key ID')]
	public function delete(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['apiKeys']);
		$id = (int) $request->getParameter('id');
		$apiKey = $this->repository->find($id);
		if ($apiKey === null) {
			throw new ClientErrorException('API key not found', ApiResponse::S404_NOT_FOUND);
		}
		$this->entityManager->remove($apiKey);
		$this->entityManager->flush();
		return $response;
	}

	#[Path('/{id}')]
	#[Method('PUT')]
	#[OpenApi(<<<'EOT'
		summary: Updates the API key
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: '#/components/schemas/ApiKeyModify'
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
	#[RequestParameter(name: 'id', type: 'integer', description: 'API key ID')]
	public function update(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['apiKeys']);
		$id = (int) $request->getParameter('id');
		$apiKey = $this->repository->find($id);
		if ($apiKey === null) {
			throw new ClientErrorException('API key not found', ApiResponse::S404_NOT_FOUND);
		}
		$this->validators->validateRequest('apiKeyModify', $request);
		$json = $request->getJsonBodyCopy(false);
		$apiKey->setDescription($json->description);
		try {
			$apiKey->setExpirationFromString($json->expiration);
		} catch (ApiKeyExpirationPassedException | ApiKeyInvalidExpirationException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S400_BAD_REQUEST, $e);
		}
		$this->entityManager->persist($apiKey);
		$this->entityManager->flush();
		return $response;
	}

}
