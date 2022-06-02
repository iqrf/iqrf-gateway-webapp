<?php

/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
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

namespace App\ApiModule\Version0\Controllers\Network;

use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\OpenApi;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Annotation\Controller\RequestParameter;
use Apitte\Core\Annotation\Controller\RequestParameters;
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Exception\Api\ClientErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Controllers\NetworkController;
use App\ApiModule\Version0\Models\RestApiSchemaValidator;
use App\Models\Database\Entities\NetworkOperator;
use App\Models\Database\EntityManager;
use App\Models\Database\Repositories\NetworkOperatorRepository;

/**
 * Network operator controller
 * @Path("/operators")
 * @Tag("Network operators")
 */
class OperatorController extends NetworkController {

	/**
	 * @var EntityManager Entity manager
	 */
	private $entityManager;

	/**
	 * @var NetworkOperatorRepository Network operator repository
	 */
	private $repository;

	/**
	 * Constructor
	 * @param EntityManager $entityManager Entity manager
	 * @param RestApiSchemaValidator $validator REST API JSON schema validator
	 */
	public function __construct(EntityManager $entityManager, RestApiSchemaValidator $validator) {
		$this->entityManager = $entityManager;
		$this->repository = $this->entityManager->getNetworkOperatorRepository();
		parent::__construct($validator);
	}

	/**
	 * @Path("/")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: Lists all network operators
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      type: array
	 *                      items:
	 *                          $ref: '#/components/schemas/NetworkOperator'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function list(ApiRequest $request, ApiResponse $response): ApiResponse {
		$operators = $this->repository->findAll();
		return $response->writeJsonBody($operators);
	}

	/**
	 * @Path("/{id}")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: Retrieves a network operator configuration
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/NetworkOperator'
	 *      '404':
	 *          description: 'Network operator not found'
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="id", type="integer", description="Operator id")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function get(ApiRequest $request, ApiResponse $response): ApiResponse {
		$id = (int) $request->getParameter('id');
		$operator = $this->repository->find($id);
		if ($operator === null) {
			throw new ClientErrorException('Network operator not found', ApiResponse::S404_NOT_FOUND);
		}
		return $response->writeJsonObject($operator);
	}

	/**
	 * @Path("/")
	 * @Method("POST")
	 * @OpenApi("
	 *  summary: Creates a new network operator
	 *  requestBody:
	 *      required: true
	 *      content:
	 *          application/json:
	 *              schema:
	 *                  $ref: '#/components/schemas/NetworkOperator'
	 *  responses:
	 *      '201':
	 *          description: Created
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/NetworkOperator'
	 *          headers:
	 *              Location:
	 *                  description: Location of information about network operator
	 *                  schema:
	 *                      type: string
	 *      '400':
	 *          $ref: '#/components/responses/BadRequest'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function create(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validator->validateRequest('networkOperator', $request);
		$json = $request->getJsonBody(false);
		$operator = new NetworkOperator($json->name, $json->apn);
		if (property_exists($json, 'username')) {
			$operator->setUsername($json->username);
		}
		if (property_exists($json, 'password')) {
			$operator->setPassword($json->password);
		}
		$this->entityManager->persist($operator);
		$this->entityManager->flush();
		return $response->writeJsonObject($operator)
			->withHeader('Location', '/api/v0/operators/' . $operator->getId())
			->withStatus(ApiResponse::S201_CREATED);
	}

	/**
	 * @Path("/{id}")
	 * @Method("PUT")
	 * @OpenApi("
	 *  summary: Edits a network operator
	 *  requestBody:
	 *      required: true
	 *      content:
	 *          application/json:
	 *              schema: '#/components/schemas/NetworkOperator'
	 *  responses:
	 *      '200':
	 *          description: Success
	 *      '400':
	 *          $ref: '#/components/responses/BadRequest'
	 *      '404':
	 *          description: 'Network operator not found'
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="id", type="integer", description="Operator id")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function edit(ApiRequest $request, ApiResponse $response): ApiResponse {
		$id = (int) $request->getParameter('id');
		$operator = $this->repository->find($id);
		if ($operator === null) {
			throw new ClientErrorException('Network operator not found', ApiResponse::S404_NOT_FOUND);
		}
		$this->validator->validateRequest('networkOperator', $request);
		$json = $request->getJsonBody(false);
		$operator->setName($json->name);
		$operator->setApn($json->apn);
		$operator->setUsername($json->username ?? null);
		$operator->setPassword($json->password ?? null);
		$this->entityManager->persist($operator);
		$this->entityManager->flush();
		return $response->writeBody('Workaround');
	}

	/**
	 * @Path("/{id}")
	 * @Method("DELETE")
	 * @OpenApi("
	 *  summary: Removes a network operator
	 *  responses:
	 *      '200':
	 *          description: Success
	 *      '404':
	 *          description: 'Network operator not found'
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="id", type="integer", description="Operator id")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function delete(ApiRequest $request, ApiResponse $response): ApiResponse {
		$id = (int) $request->getParameter('id');
		$operator = $this->repository->find($id);
		if ($operator === null) {
			throw new ClientErrorException('Network operator not found', ApiResponse::S404_NOT_FOUND);
		}
		$this->entityManager->remove($operator);
		$this->entityManager->flush();
		return $response->writeBody('Workaround');
	}

}
