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
use App\ConfigModule\Models\ComponentManager;
use App\ConfigModule\Models\GenericManager;
use App\ConfigModule\Models\MainManager;
use App\CoreModule\Exceptions\InvalidJsonException;
use App\CoreModule\Exceptions\NonexistentJsonSchemaException;
use Nette\IOException;
use Nette\Utils\JsonException;

/**
 * Configuration controller
 * @Path("/daemon")
 * @Tag("IQRF Gateway Daemon configuration")
 */
class DaemonController extends BaseConfigController {

	/**
	 * @var ComponentManager Component configuration manager
	 */
	private ComponentManager $componentManager;

	/**
	 * @var MainManager Main configuration manager
	 */
	private MainManager $mainManager;

	/**
	 * @var GenericManager Configuration manager
	 */
	private GenericManager $manager;

	/**
	 * Constructor
	 * @param ComponentManager $componentManager Component configuration manager
	 * @param MainManager $mainManager Main configuration manager
	 * @param GenericManager $manager Configuration manager
	 * @param RestApiSchemaValidator $validator REST API JSON schema validator
	 */
	public function __construct(ComponentManager $componentManager, MainManager $mainManager, GenericManager $manager, RestApiSchemaValidator $validator) {
		$this->componentManager = $componentManager;
		$this->mainManager = $mainManager;
		$this->manager = $manager;
		parent::__construct($validator);
	}

	/**
	 * @Path("/")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: Returns main configuration
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/MainConfiguration'
	 *      '403':
	 *          $ref: '#/components/responses/Forbidden'
	 *      '500':
	 *          $ref: '#/components/responses/ServerError'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function get(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['config:daemon']);
		try {
			$config = $this->mainManager->load();
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
	 *  summary: Edits main configuration
	 *  requestBody:
	 *      required: true
	 *      content:
	 *          application/json:
	 *              schema:
	 *                  $ref: '#/components/schemas/MainConfiguration'
	 *  responses:
	 *      '200':
	 *          description: Success
	 *      '400':
	 *          $ref: '#/components/responses/BadRequest'
	 *      '403':
	 *          $ref: '#/components/responses/Forbidden'
	 *      '404':
	 *          description: Not found
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function edit(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['config:daemon']);
		$this->validator->validateRequest('mainConfiguration', $request);
		try {
			$this->mainManager->save($request->getJsonBody(true));
			return $response->writeBody('Workaround');
		} catch (IOException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	/**
	 * @Path("/")
	 * @Method("POST")
	 * @OpenApi("
	 *  summary: Creates component configuration
	 *  requestBody:
	 *      required: true
	 *      content:
	 *          application/json:
	 *              schema:
	 *                  $ref: '#/components/schemas/DaemonComponent'
	 *  responses:
	 *      '201':
	 *          description: Created
	 *      '400':
	 *          $ref: '#/components/responses/BadRequest'
	 *      '403':
	 *          $ref: '#/components/responses/Forbidden'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function createComponent(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['config:daemon']);
		$this->validator->validateRequest('daemonComponent', $request);
		try {
			$this->componentManager->add($request->getJsonBody(true));
			return $response->withStatus(ApiResponse::S201_CREATED)
				->writeBody('Workaround');
		} catch (IOException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	/**
	 * @Path("/{component}")
	 * @Method("DELETE")
	 * @OpenApi("
	 *  summary: Deletes the component
	 *  responses:
	 *      '200':
	 *          description: Success
	 *      '403':
	 *          $ref: '#/components/responses/Forbidden'
	 *      '404':
	 *          description: Not found
	 *      '500':
	 *          $ref: '#/components/responses/ServerError'
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="component", type="string", description="Component name")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function deleteComponent(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['config:daemon']);
		$component = urldecode($request->getParameter('component'));
		$id = $this->componentManager->getId($component);
		if ($id === null) {
			throw new ClientErrorException('Component not found', ApiResponse::S404_NOT_FOUND);
		}
		try {
			$this->componentManager->delete($id);
			return $response->writeBody('Workaround');
		} catch (JsonException $e) {
			throw new ServerErrorException('Invalid JSON syntax', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		} catch (IOException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	/**
	 * @Path("/{component}")
	 * @Method("PUT")
	 * @OpenApi("
	 *  summary: Edits the component
	 *  requestBody:
	 *      required: true
	 *      content:
	 *          application/json:
	 *              schema:
	 *                  $ref: '#/components/schemas/DaemonComponent'
	 *  responses:
	 *      '200':
	 *          description: Success
	 *      '400':
	 *          $ref: '#/components/responses/BadRequest'
	 *      '403':
	 *          $ref: '#/components/responses/Forbidden'
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="component", type="string", description="Component name")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function editComponent(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['config:daemon']);
		$component = urldecode($request->getParameter('component'));
		$id = $this->componentManager->getId($component);
		if ($id === null) {
			throw new ClientErrorException('Component not found', ApiResponse::S404_NOT_FOUND);
		}
		$this->validator->validateRequest('daemonComponent', $request);
		try {
			$this->componentManager->save($request->getJsonBody(), $id);
		} catch (IOException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
		return $response->writeBody('Workaround');
	}

	/**
	 * @Path("/{component}")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: Returns the component configuration and instances of the component
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/DaemonComponentDetail'
	 *      '403':
	 *          $ref: '#/components/responses/Forbidden'
	 *      '404':
	 *          description: Not found
	 *      '500':
	 *          $ref: '#/components/responses/ServerError'
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="component", type="string", description="Component name")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function getComponent(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['config:daemon']);
		$component = urldecode($request->getParameter('component'));
		try {
			$this->manager->setComponent($component);
		} catch (NonexistentJsonSchemaException $e) {
			throw new ServerErrorException('Missing JSON schema', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
		$id = $this->componentManager->getId($component);
		if ($id === null) {
			throw new ClientErrorException('Component not found', ApiResponse::S404_NOT_FOUND);
		}
		try {
			$body = [
				'configuration' => $this->componentManager->load($id),
				'instances' => $this->manager->list(),
			];
			return $response->writeJsonBody($body);
		} catch (JsonException $e) {
			throw new ServerErrorException('Invalid JSON syntax', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		} catch (IOException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	/**
	 * @Path("/{component}")
	 * @Method("POST")
	 * @OpenApi("
	 *  summary: Creates instance configuration
	 *  requestBody:
	 *      required: true
	 *      content:
	 *          application/json:
	 *              schema:
	 *                  $ref: '#/components/schemas/DaemonConfiguration'
	 *  responses:
	 *      '201':
	 *          description: Created
	 *      '400':
	 *          $ref: '#/components/responses/BadRequest'
	 *      '403':
	 *          $ref: '#/components/responses/Forbidden'
	 *      '404':
	 *          description: Not found
	 *      '409':
	 *          description: Instance already exists
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="component", type="string", description="Component name")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function createInstance(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['config:daemon']);
		try {
			$json = $request->getJsonBody(true);
			$component = urldecode($request->getParameter('component'));
			$this->manager->setComponent($component);
			if (!isset($json['instance'])) {
				throw new ClientErrorException('Missing instance name', ApiResponse::S400_BAD_REQUEST);
			}
			$fileName = $this->manager->getInstanceFileName($json['instance']);
			if ($fileName !== null) {
				throw new ClientErrorException('Instance already exists', ApiResponse::S409_CONFLICT);
			} else {
				$fileName = $this->manager->generateFileName($json);
			}
			$this->manager->save($json, $fileName);
			return $response->withStatus(ApiResponse::S201_CREATED)
				->writeBody('Workaround');
		} catch (NonexistentJsonSchemaException $e) {
			throw new ServerErrorException('Missing JSON schema for the component', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		} catch (JsonException $e) {
			throw new ClientErrorException('Invalid JSON syntax', ApiResponse::S400_BAD_REQUEST, $e);
		} catch (InvalidJsonException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S400_BAD_REQUEST, $e);
		} catch (IOException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	/**
	 * @Path("/{component}/{instance}")
	 * @Method("DELETE")
	 * @OpenApi("
	 *  summary: Deletes instance configuration by name
	 *  responses:
	 *      '200':
	 *          description: Success
	 *      '403':
	 *          $ref: '#/components/responses/Forbidden'
	 *      '404':
	 *          description: Not found
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="component", type="string", description="Component name"),
	 *      @RequestParameter(name="instance", type="string", description="Instance name")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function deleteInstance(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['config:daemon']);
		$component = urldecode($request->getParameter('component'));
		$this->manager->setComponent($component);
		$instance = urldecode($request->getParameter('instance'));
		$fileName = $this->manager->getInstanceFileName($instance);
		if ($fileName === null) {
			throw new ClientErrorException('Component not found', ApiResponse::S404_NOT_FOUND);
		}
		$this->manager->deleteFile($fileName);
		return $response->writeBody('Workaround');
	}

	/**
	 * @Path("/{component}/{instance}")
	 * @Method("PUT")
	 * @OpenApi("
	 *  summary: Edits instance configuration by name
	 *  requestBody:
	 *      required: true
	 *      content:
	 *          application/json:
	 *              schema:
	 *                  $ref: '#/components/schemas/DaemonConfiguration'
	 *  responses:
	 *      '200':
	 *          description: Succcess
	 *      '400':
	 *          $ref: '#/components/responses/BadRequest'
	 *      '403':
	 *          $ref: '#/components/responses/Forbidden'
	 *      '404':
	 *          description: Not found
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="component", type="string", description="Component name"),
	 *      @RequestParameter(name="instance", type="string", description="Instance name")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function editInstance(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['config:daemon']);
		try {
			$json = $request->getJsonBody(true);
			$component = urldecode($request->getParameter('component'));
			$this->manager->setComponent($component);
			if (!isset($json['instance'])) {
				throw new ClientErrorException('Missing instance name', ApiResponse::S400_BAD_REQUEST);
			}
			$instance = urldecode($request->getParameter('instance'));
			$fileName = $this->manager->getInstanceFileName($instance);
			$this->manager->save($json, $fileName);
		} catch (NonexistentJsonSchemaException $e) {
			throw new ClientErrorException('Component not found', ApiResponse::S404_NOT_FOUND, $e);
		} catch (JsonException $e) {
			throw new ClientErrorException('Invalid JSON syntax', ApiResponse::S400_BAD_REQUEST, $e);
		} catch (InvalidJsonException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S400_BAD_REQUEST, $e);
		} catch (IOException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
		return $response->writeBody('Workaround');
	}

	/**
	 * @Path("/{component}/{instance}")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: Finds instance configuration by name
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/DaemonConfiguration'
	 *      '403':
	 *          $ref: '#/components/responses/Forbidden'
	 *      '404':
	 *          description: Not found
	 *      '500':
	 *          $ref: '#/components/responses/ServerError'
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="component", type="string", description="Component name"),
	 *      @RequestParameter(name="instance", type="string", description="Instance name")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function getInstance(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['config:daemon']);
		try {
			$component = urldecode($request->getParameter('component'));
			$this->manager->setComponent($component);
			$instance = urldecode($request->getParameter('instance'));
			$configuration = $this->manager->loadInstance($instance);
		} catch (NonexistentJsonSchemaException $e) {
			throw new ServerErrorException('Missing JSON schema for the component', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		} catch (JsonException $e) {
			throw new ServerErrorException('Invalid JSON syntax', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		} catch (IOException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
		if ($configuration === []) {
			throw new ClientErrorException('Instance not found', ApiResponse::S404_NOT_FOUND);
		}
		return $response->writeJsonBody($configuration);
	}

	/**
	 * @Path("/components")
	 * @Method("PATCH")
	 * @OpenApi("
	 *  summary: 'Changes enabled state of component(s)'
	 *  requestBody:
	 *      required: true
	 *      content:
	 *          application/json:
	 *              schema:
	 *                  $ref: '#/components/schemas/DaemonComponentEnabled'
	 *  responses:
	 *      '200':
	 *          description: Success
	 *      '403':
	 *          $ref: '#/components/responses/Forbidden'
	 *      '404':
	 *          description: Not found
	 *      '500':
	 *          $ref: '#/components/responses/ServerError'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function changeComponent(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['config:daemon']);
		$this->validator->validateRequest('daemonComponentEnabled', $request);
		try {
			$reqData = $request->getJsonBody(true);
			$config = $this->mainManager->load();
			foreach ($reqData as $component) {
				$index = array_search($component['name'], array_column($config['components'], 'name'), true);
				if (!$index) {
					throw new ClientErrorException('Component ' . $component['name'] . ' missing in daemon configuration.', ApiResponse::S404_NOT_FOUND);
				}
				$config['components'][$index]['enabled'] = $component['enabled'];
			}
			$this->mainManager->save($config);
			return $response->writeBody('Workaround');
		} catch (IOException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

}
