<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2019 IQRF Tech s.r.o.
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
use Apitte\Core\Annotation\Controller\Response;
use Apitte\Core\Annotation\Controller\Responses;
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\Models\Database\Entities\ApiKey;
use App\Models\Database\EntityManager;
use function assert;

/**
 * API keys controller
 * @Path("/apiKeys")
 * @Tag("API key manager")
 */
class ApiKeyController extends BaseController {

	/**
	 * @var EntityManager Entity manager
	 */
	private $entityManager;

	/**
	 * Constructor
	 * @param EntityManager $entityManager Entity manager
	 */
	public function __construct(EntityManager $entityManager) {
		$this->entityManager = $entityManager;
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
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function list(ApiRequest $request, ApiResponse $response): ApiResponse {
		$repository = $this->entityManager->getApiKeyRepository();
		$apiKeys = $repository->findAll();
		return $response->writeJsonBody($apiKeys);
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
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="id", type="integer", description="API key ID")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function get(ApiRequest $request, ApiResponse $response): ApiResponse {
		$repository = $this->entityManager->getApiKeyRepository();
		$apiKey = $repository->find((int) $request->getParameter('id'));
		if ($apiKey === null) {
			return $response->withStatus(404);
		}
		assert($apiKey instanceof ApiKey);
		return $response->writeJsonObject($apiKey);
	}

	/**
	 * @Path("/{id}")
	 * @Method("DELETE")
	 * @OpenApi("
	 *   summary: Deletes a API key
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="id", type="integer", description="API key ID")
	 * })
	 * @Responses({
	 *      @Response(code="200", description="Success"),
	 *      @Response(code="404", description="Not found")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function delete(ApiRequest $request, ApiResponse $response): ApiResponse {
		$repository = $this->entityManager->getApiKeyRepository();
		$apiKey = $repository->find((int) $request->getParameter('id'));
		if ($apiKey === null) {
			return $response->withStatus(404);
		}
		$this->entityManager->remove($apiKey);
		$this->entityManager->flush();
		return $response;
	}

}
