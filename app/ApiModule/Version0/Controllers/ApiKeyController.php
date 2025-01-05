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
use Apitte\Core\Annotation\Controller\RequestParameters;
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Exception\Api\ClientErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Models\RestApiSchemaValidator;
use App\Exceptions\ApiKeyExpirationPassedException;
use App\Exceptions\ApiKeyInvalidExpirationException;
use App\Models\Database\Entities\ApiKey;
use App\Models\Database\EntityManager;
use App\Models\Database\Repositories\ApiKeyRepository;

/**
 * API keys controller
 * @Path("/apiKeys")
 * @Tag("API key manager")
 */
class ApiKeyController extends BaseController {

	/**
	 * @var EntityManager Entity manager
	 */
	private EntityManager $entityManager;

	/**
	 * @var ApiKeyRepository API key database repository
	 */
	private ApiKeyRepository $repository;

	/**
	 * Constructor
	 * @param EntityManager $entityManager Entity manager
	 * @param RestApiSchemaValidator $validator REST API JSON schema validator
	 */
	public function __construct(EntityManager $entityManager, RestApiSchemaValidator $validator) {
		$this->entityManager = $entityManager;
		$this->repository = $entityManager->getApiKeyRepository();
		parent::__construct($validator);
	}

	/**
	 * @Path("/")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: Lists all API keys
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      type: array
	 *                      items:
	 *                          $ref: '#/components/schemas/ApiKeyDetail'
	 *      '403':
	 *          $ref: '#/components/responses/Forbidden'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function list(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['apiKeys']);
		$apiKeys = $this->repository->findAll();
		return $response->writeJsonBody($apiKeys);
	}

	/**
	 * @Path("/")
	 * @Method("POST")
	 * @OpenApi("
	 *  summary: Creates a new API key
	 *  requestBody:
	 *      required: true
	 *      content:
	 *          application/json:
	 *              schema:
	 *                  $ref: '#/components/schemas/ApiKeyModify'
	 *  responses:
	 *      '201':
	 *          description: Created
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/ApiKeyCreated'
	 *          headers:
	 *              Location:
	 *                  description: Location of information about the created API key
	 *                  schema:
	 *                      type: string
	 *      '400':
	 *          $ref: '#/components/responses/BadRequest'
	 *      '403':
	 *          $ref: '#/components/responses/Forbidden'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function create(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['apiKeys']);
		$this->validator->validateRequest('apiKeyModify', $request);
		$json = $request->getJsonBody(false);
		$apiKey = new ApiKey($json->description, null);
		try {
			$apiKey->setExpirationFromString($json->expiration);
		} catch (ApiKeyExpirationPassedException | ApiKeyInvalidExpirationException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S400_BAD_REQUEST, $e);
		}
		$this->entityManager->persist($apiKey);
		$this->entityManager->flush();
		return $response->writeJsonObject($apiKey)
			->withHeader('Location', '/api/v0/apiKeys/' . $apiKey->getId())
			->withStatus(ApiResponse::S201_CREATED);
	}

	/**
	 * @Path("/{id}")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: Finds API key by ID
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/ApiKeyDetail'
	 *      '404':
	 *          description: Not found
	 *      '403':
	 *          $ref: '#/components/responses/Forbidden'
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="id", type="integer", description="API key ID")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function get(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['apiKeys']);
		$id = (int) $request->getParameter('id');
		$apiKey = $this->repository->find($id);
		if ($apiKey === null) {
			throw new ClientErrorException('API key not found', ApiResponse::S404_NOT_FOUND);
		}
		return $response->writeJsonObject($apiKey);
	}

	/**
	 * @Path("/{id}")
	 * @Method("DELETE")
	 * @OpenApi("
	 *  summary: Deletes a API key
	 *  responses:
	 *      '200':
	 *          description: Success
	 *      '403':
	 *          $ref: '#/components/responses/Forbidden'
	 *      '404':
	 *          description: Not found
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="id", type="integer", description="API key ID")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function delete(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['apiKeys']);
		$id = (int) $request->getParameter('id');
		$apiKey = $this->repository->find($id);
		if ($apiKey === null) {
			throw new ClientErrorException('API key not found', ApiResponse::S404_NOT_FOUND);
		}
		$this->entityManager->remove($apiKey);
		$this->entityManager->flush();
		return $response->writeBody('Workaround');
	}

	/**
	 * @Path("/{id}")
	 * @Method("PUT")
	 * @OpenApi("
	 *  summary: Edits the API key
	 *  requestBody:
	 *      required: true
	 *      content:
	 *          application/json:
	 *              schema:
	 *                  $ref: '#/components/schemas/ApiKeyModify'
	 *  responses:
	 *      '200':
	 *          description: Success
	 *      '400':
	 *          $ref: '#/components/responses/BadRequest'
	 *      '403':
	 *          $ref: '#/components/responses/Forbidden'
	 *      '404':
	 *          description: 'API key not found'
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="id", type="integer", description="API key ID")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function edit(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['apiKeys']);
		$id = (int) $request->getParameter('id');
		$apiKey = $this->repository->find($id);
		if ($apiKey === null) {
			throw new ClientErrorException('API key not found', ApiResponse::S404_NOT_FOUND);
		}
		$this->validator->validateRequest('apiKeyModify', $request);
		$json = $request->getJsonBody(false);
		$apiKey->setDescription($json->description);
		try {
			$apiKey->setExpirationFromString($json->expiration);
		} catch (ApiKeyExpirationPassedException | ApiKeyInvalidExpirationException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S400_BAD_REQUEST, $e);
		}
		$this->entityManager->persist($apiKey);
		$this->entityManager->flush();
		return $response->writeBody('Workaround');
	}

}
