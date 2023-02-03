<?php

/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
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

namespace App\ApiModule\Version0\Controllers\Config;

use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\OpenApi;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Annotation\Controller\RequestParameter;
use Apitte\Core\Annotation\Controller\RequestParameters;
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Exception\Api\ClientErrorException;
use Apitte\Core\Exception\Api\ServerErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Controllers\BaseConfigController;
use App\ApiModule\Version0\Models\RestApiSchemaValidator;
use App\ConfigModule\Exceptions\ControllerPinConfigNotFoundException;
use App\ConfigModule\Models\ControllerConfigManager;
use App\ConfigModule\Models\ControllerPinConfigManager;
use Nette\IOException;
use Nette\Utils\JsonException;

/**
 * IQRF Gateway Controller configuration controller
 * @Path("/controller")
 * @Tag("IQRF Gateway Controller configuration")
 */
class ControllerController extends BaseConfigController {

	/**
	 * @var ControllerConfigManager $configManager IQRF Gateway Controller configuration manager
	 */
	private ControllerConfigManager $configManager;

	/**
	 * @var ControllerPinConfigManager $pinManager IQRF Gateway Controller pin configuration manager
	 */
	private ControllerPinConfigManager $pinManager;

	/**
	 * Constructor
	 * @param ControllerConfigManager $configManager IQRF Gateway Controller configuration manager
	 * @param ControllerPinConfigManager $pinManager IQRF Gateway Controller pin configuration manager
	 * @param RestApiSchemaValidator $validator REST API JSON schema validator
	 */
	public function __construct(ControllerConfigManager $configManager, ControllerPinConfigManager $pinManager, RestApiSchemaValidator $validator) {
		$this->configManager = $configManager;
		$this->pinManager = $pinManager;
		parent::__construct($validator);
	}

	/**
	 * @Path("/")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: Returns current configuration of IQRF Gateway Controller
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/ControllerConfig'
	 *      '403':
	 *          $ref: '#/components/responses/Forbidden'
	 *      '500':
	 *          $ref: '#/components/responses/ServerError'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function getConfig(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['config:controller']);
		try {
			$config = $this->configManager->getConfig();
			return $response->writeJsonBody($config);
		} catch (JsonException $e) {
			throw new ServerErrorException('Invalid JSON syntax', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		} catch (IOException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	/**
	 * @Path("/")
	 * @Method("PUT")
	 * @OpenApi("
	 *  summary: Saves new configuration of IQRF Gateway Controller
	 *  requestBody:
	 *      required: true
	 *      content:
	 *          application/json:
	 *              schema:
	 *                  $ref: '#/components/schemas/ControllerConfig'
	 *  responses:
	 *      '200':
	 *          description: Success
	 *      '400':
	 *          $ref: '#/components/responses/BadRequest'
	 *      '403':
	 *          $ref: '#/components/responses/Forbidden'
	 *      '500':
	 *          $ref: '#/components/responses/ServerError'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function setConfig(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['config:controller']);
		$this->validator->validateRequest('controllerConfig', $request);
		try {
			$this->configManager->saveConfig($request->getJsonBody());
			return $response->writeBody('Workaround');
		} catch (IOException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	/**
	 * @Path("/pins")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: List all pin configurations
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      type: array
	 *                      items:
	 *                          $ref: '#/components/schemas/ControllerPinConfig'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function listPins(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $response->writeJsonBody($this->pinManager->listPinConfigs());
	}

	/**
	 * @Path("/pins/{id}")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: Returns a pin configuration profile
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/ControllerPinConfig'
	 *      '404':
	 *          description: Pin configuration profile not found
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="id", type="integer", description="Controller pin configuration profile ID")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function getPins(ApiRequest $request, ApiResponse $response): ApiResponse {
		$id = (int) $request->getParameter('id');
		try {
			$entity = $this->pinManager->getPinConfig($id);
			return $response->writeJsonObject($entity);
		} catch (ControllerPinConfigNotFoundException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S404_NOT_FOUND, $e);
		}
	}

	/**
	 * @Path("/pins")
	 * @Method("POST")
	 * @OpenApi("
	 *  summary: Creates a new pin configuration profile
	 *  requestBody:
	 *      required: true
	 *      content:
	 *          application/json:
	 *              schema:
	 *                  $ref: '#/components/schemas/ControllerPinConfig'
	 *  responses:
	 *      '201':
	 *          description: Created
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/ControllerPinConfig'
	 *          headers:
	 *              Location:
	 *                  description: Location of information about created pin configuration profile
	 *                  schema:
	 *                      type: string
	 *      '400':
	 *          $ref: '#/components/responses/BadRequest'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function addPins(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validator->validateRequest('controllerPinConfig', $request);
		$json = $request->getJsonBody(false);
		$entity = $this->pinManager->addPinConfig($json);
		return $response->writeJsonObject($entity)
			->withHeader('Location', '/api/v0/config/controller/pins/' . $entity->getId())
			->withStatus(ApiResponse::S201_CREATED);
	}

	/**
	 * @Path("/pins/{id}")
	 * @Method("PUT")
	 * @OpenApi("
	 *  summary: Edits a pin configuration profile
	 *  responses:
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="id", type="integer", description="Controller pin configuration profile ID")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function editPins(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validator->validateRequest('controllerPinConfig', $request);
		$id = (int) $request->getParameter('id');
		$json = $request->getJsonBody(false);
		try {
			$this->pinManager->editPinConfig($id, $json);
			return $response->writeBody('Workaround');
		} catch (ControllerPinConfigNotFoundException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S404_NOT_FOUND, $e);
		}
	}

	/**
	 * @Path("/pins/{id}")
	 * @Method("DELETE")
	 * @OpenApi("
	 *  summary: Removes a pin configuration profile
	 *  responses:
	 *      '200':
	 *          description: Success
	 *      '404':
	 *          description: Controller pin configuration profile not found
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="id", type="integer", description="Controller pin configuration profile ID")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function removePins(ApiRequest $request, ApiResponse $response): ApiResponse {
		$id = (int) $request->getParameter('id');
		try {
			$this->pinManager->removePinConfig($id);
			return $response->writeBody('Workaround');
		} catch (ControllerPinConfigNotFoundException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S404_NOT_FOUND, $e);
		}
	}

}
