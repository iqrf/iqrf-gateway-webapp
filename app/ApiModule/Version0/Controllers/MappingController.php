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
use App\Models\Database\Entities\Mapping;
use App\Models\Database\EntityManager;
use App\Models\Database\Repositories\MappingRepository;

/**
 * Mapping controller
 * @Path("/mappings")
 * @Tag("Mapping manager")
 */
class MappingController extends BaseController {

	/**
	 * @var EntityManager Entity manager
	 */
	private EntityManager $entityManager;

	/**
	 * @var MappingRepository Mapping database repository
	 */
	private MappingRepository $repository;

	/**
	 * Constructor
	 * @param EntityManager $entityManager Entity manager
	 * @param RestApiSchemaValidator $validator REST API JSON schema validator
	 */
	public function __construct(EntityManager $entityManager, RestApiSchemaValidator $validator) {
		$this->entityManager = $entityManager;
		$this->repository = $this->entityManager->getMappingRepository();
		parent::__construct($validator);
	}

	/**
	 * @Path("/")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: Lists all mappings
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      type: array
	 *                      items:
	 *                          $ref: '#/components/schemas/MappingDetail'
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="interface", in="query", type="string", description="Interface type", required="false")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function list(ApiRequest $request, ApiResponse $response): ApiResponse {
		$criteria = !$request->hasQueryParam('interface') ? [] : ['type' => $request->getQueryParam('interface')];
		$mappings = $this->repository->findBy($criteria);
		return $response->writeJsonBody($mappings);
	}

	/**
	 * @Path("/")
	 * @Method("POST")
	 * @OpenApi("
	 *  summary: Creates a new mapping
	 *  requestBody:
	 *      required: true
	 *      content:
	 *          application/json:
	 *              schema:
	 *                  $ref: '#/components/schemas/Mapping'
	 *  responses:
	 *      '201':
	 *          description: Created
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/MappingDetail'
	 *          headers:
	 *              Location:
	 *                  description: Location of information about the created mapping
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
		$this->validator->validateRequest('mapping', $request);
		$json = $request->getJsonBody(false);
		if ($json->type === Mapping::TYPE_UART) {
			$mapping = new Mapping(
				$json->type,
				$json->name,
				$json->deviceType,
				$json->IqrfInterface,
				$json->busEnableGpioPin,
				$json->pgmSwitchGpioPin,
				$json->powerEnableGpioPin,
				$json->baudRate
			);
		} else {
			$mapping = new Mapping(
				$json->type,
				$json->name,
				$json->deviceType,
				$json->IqrfInterface,
				$json->busEnableGpioPin,
				$json->pgmSwitchGpioPin,
				$json->powerEnableGpioPin
			);
		}
		if (property_exists($json, 'i2cEnableGpioPin')) {
			$mapping->setI2cPin($json->i2cEnableGpioPin);
		}
		if (property_exists($json, 'spiEnableGpioPin')) {
			$mapping->setSpiPin($json->spiEnableGpioPin);
		}
		if (property_exists($json, 'uartEnableGpioPin')) {
			$mapping->setUartPin($json->uartEnableGpioPin);
		}
		$this->entityManager->persist($mapping);
		$this->entityManager->flush();
		return $response->writeJsonObject($mapping)
			->withHeader('Location', '/api/v0/mappings/' . $mapping->getId())
			->withStatus(ApiResponse::S201_CREATED);
	}

	/**
	 * @Path("/{id}")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: Finds mapping by ID
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/MappingDetail'
	 *      '404':
	 *          description: Mapping not found
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="id", type="integer", description="Mapping id")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function get(ApiRequest $request, ApiResponse $response): ApiResponse {
		$id = (int) $request->getParameter('id');
		$mapping = $this->repository->find($id);
		if ($mapping === null) {
			throw new ClientErrorException('Mapping not found', ApiResponse::S404_NOT_FOUND);
		}
		return $response->writeJsonObject($mapping);
	}

	/**
	 * @Path("/{id}")
	 * @Method("DELETE")
	 * @OpenApi("
	 *  summary: Removes a mapping
	 *  responses:
	 *      '200':
	 *          description: Success
	 *      '404':
	 *          description: Mapping not found
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="id", type="integer", description="Mapping id")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function delete(ApiRequest $request, ApiResponse $response): ApiResponse {
		$id = (int) $request->getParameter('id');
		$mapping = $this->repository->find($id);
		if ($mapping === null) {
			throw new ClientErrorException('Mapping not found', ApiResponse::S404_NOT_FOUND);
		}
		$this->entityManager->remove($mapping);
		$this->entityManager->flush();
		return $response->writeBody('Workaround');
	}

	/**
	 * @Path("/{id}")
	 * @Method("PUT")
	 * @OpenApi("
	 *  summary: Edits a mapping
	 *  requestBody:
	 *      required: true
	 *      content:
	 *          application/json:
	 *              schema:
	 *                  $ref: '#/components/schemas/Mapping'
	 *  responses:
	 *      '200':
	 *          description: Success
	 *      '400':
	 *          $ref: '#/components/responses/BadRequest'
	 *      '404':
	 *          description: 'Mapping not found'
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="id", type="integer", description="Mapping ID")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function edit(ApiRequest $request, ApiResponse $response): ApiResponse {
		$id = (int) $request->getParameter('id');
		$mapping = $this->repository->find($id);
		if ($mapping === null) {
			throw new ClientErrorException('Mapping not found', ApiResponse::S404_NOT_FOUND);
		}
		$this->validator->validateRequest('mapping', $request);
		$json = $request->getJsonBody(false);
		$mapping->setName($json->name);
		$mapping->setDeviceType($json->deviceType);
		$mapping->setInterface($json->IqrfInterface);
		$mapping->setBusPin($json->busEnableGpioPin);
		$mapping->setPgmPin($json->pgmSwitchGpioPin);
		$mapping->setPowerPin($json->powerEnableGpioPin);
		$mapping->setType($json->type);
		if ($json->type === Mapping::TYPE_UART) {
			$mapping->setBaudRate($json->baudRate);
		}
		if (property_exists($json, 'i2cEnableGpioPin')) {
			$mapping->setI2cPin($json->i2cEnableGpioPin);
		} else {
			$mapping->setI2cPin();
		}
		if (property_exists($json, 'spiEnableGpioPin')) {
			$mapping->setSpiPin($json->spiEnableGpioPin);
		} else {
			$mapping->setSpiPin();
		}
		if (property_exists($json, 'uartEnableGpioPin')) {
			$mapping->setUartPin($json->uartEnableGpioPin);
		} else {
			$mapping->setUartPin();
		}
		$this->entityManager->persist($mapping);
		$this->entityManager->flush();
		return $response->writeBody('Workaround');
	}

}
