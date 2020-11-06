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
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Exception\Api\ClientErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Models\RestApiSchemaValidator;
use App\CoreModule\Exceptions\InvalidJsonException;
use App\Models\Database\Entities\Mapping;
use App\Models\Database\EntityManager;
use App\Models\Database\Repositories\MappingRepository;
use Nette\Utils\JsonException;
use function assert;

/**
 * Mapping controller
 * @Path("/mappings")
 * @Tag("Mapping manager")
 */
class MappingController extends BaseController {

	/**
	 * @var EntityManager Entity manager
	 */
	private $entityManager;

	/**
	 * @var MappingRepository Mapping database repository
	 */
	private $repository;

	/**
	 * @var RestApiSchemaValidator REST API JSON schem validator
	 */
	private $validator;

	/**
	 * Constructor
	 * @param EntityManager $entityManager Entity manager
	 * @param RestApiSchemaValidator $validator REST API JSON schema validator
	 */
	public function __construct(EntityManager $entityManager, RestApiSchemaValidator $validator) {
		$this->entityManager = $entityManager;
		$this->repository = $this->entityManager->getMappingRepository();
		$this->validator = $validator;
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
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function list(ApiRequest $request, ApiResponse $response): ApiResponse {
		$mappings = $this->repository->findAll();
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
	 *                      $ref: '#/components/schemas/Mapping'
	 *      '400':
	 *          $ref: '#/components/responses/BadRequest'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function create(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			$json = $request->getJsonBody(false);
			$this->validator->validateRequest('mapping', $request);
		} catch (JsonException $e) {
			throw new ClientErrorException('Invalid JSON syntax', ApiResponse::S400_BAD_REQUEST);
		} catch (InvalidJsonException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S400_BAD_REQUEST);
		}
		if ($json->type === Mapping::TYPE_UART) {
			$mapping = new Mapping($json->type, $json->name, $json->IqrfInterface, $json->busEnableGpioPin, $json->pgmSwitchGpioPin, $json->powerEnableGpioPin, $json->baudRate);
		} else {
			$mapping = new Mapping($json->type, $json->name, $json->IqrfInterface, $json->busEnableGpioPin, $json->pgmSwitchGpioPin, $json->powerEnableGpioPin);
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
		assert($mapping instanceof Mapping);
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
		assert($mapping instanceof Mapping);
		try {
			$this->validator->validateRequest('mapping', $request);
			$json = $request->getJsonBody(false);
		} catch (JsonException $e) {
			throw new ClientErrorException('Invalid JSON syntax', ApiResponse::S400_BAD_REQUEST);
		} catch (InvalidJsonException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S400_BAD_REQUEST);
		}
		$mapping->setName($json->name);
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
		}
		if (property_exists($json, 'spiEnableGpioPin')) {
			$mapping->setSpiPin($json->spiEnableGpioPin);
		}
		if (property_exists($json, 'uartEnableGpioPin')) {
			$mapping->setUartPin($json->uartEnableGpioPin);
		}
		$this->entityManager->persist($mapping);
		$this->entityManager->flush();
		return $response->writeBody('Workaround');
	}

}
