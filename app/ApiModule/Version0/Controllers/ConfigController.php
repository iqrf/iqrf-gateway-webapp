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
use App\ConfigModule\Models\ComponentManager;
use App\ConfigModule\Models\GenericManager;
use App\ConfigModule\Models\MainManager;
use App\CoreModule\Exceptions\InvalidJsonException;
use App\CoreModule\Exceptions\NonExistingJsonSchemaException;
use Nette\Utils\JsonException;

/**
 * Configuration controller
 * @Path("/config")
 * @Tag("Config manager")
 */
class ConfigController extends BaseController {

	/**
	 * @var ComponentManager Component configuration manager
	 */
	private $componentManager;

	/**
	 * @var MainManager Main configuration manager
	 */
	private $mainManager;

	/**
	 * @var GenericManager Configuration manager
	 */
	private $manager;

	/**
	 * Constructor
	 * @param ComponentManager $componentManager Component configuration manager
	 * @param MainManager $mainManager Main configuration manager
	 * @param GenericManager $manager Configuration manager
	 */
	public function __construct(ComponentManager $componentManager, MainManager $mainManager, GenericManager $manager) {
		$this->componentManager = $componentManager;
		$this->mainManager = $mainManager;
		$this->manager = $manager;
	}

	/**
	 * @Path("/")
	 * @Method("GET")
	 * @OpenApi("
	 *   summary: Returns main configuration
	 * ")
	 * @Responses({
	 *      @Response(code="200", description="Success"),
	 *      @Response(code="500", description="Server error")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function get(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			$config = $this->mainManager->load();
			return $response->writeJsonBody($config);
		} catch (JsonException $e) {
			return $response->withStatus(500, 'Invalid JSON syntax');
		}
	}

	/**
	 * @Path("/")
	 * @Method("PUT")
	 * @OpenApi("
	 *   summary: Edits main configuration
	 *   requestBody:
	 *     required: true
	 *     content:
	 *       application/json:
	 *         schema:
	 *           type: string
	 * ")
	 * @Responses({
	 *      @Response(code="200", description="Success"),
	 *      @Response(code="400", description="Bad request"),
	 *      @Response(code="404", description="Not found")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function edit(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			$json = $request->getJsonBody(true);
			$this->mainManager->save($json);
			return $response;
		} catch (JsonException $e) {
			return $response->withStatus(400, 'Invalid JSON syntax');
		}
	}

	/**
	 * @Path("/")
	 * @Method("POST")
	 * @OpenApi("
	 *   summary: Creates component configuration
	 *   requestBody:
	 *     required: true
	 *     content:
	 *      application/json:
	 *          schema:
	 *              type: string
	 * ")
	 * @Responses({
	 *     @Response(code="201", description="Created"),
	 *     @Response(code="400", description="Bad request")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function createComponent(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			$json = $request->getJsonBody(true);
			$this->componentManager->add($json);
			return $response->withStatus(201);
		} catch (JsonException $e) {
			return $response->withStatus(400, 'Invalid JSON syntax');
		}
	}

	/**
	 * @Path("/{component}")
	 * @Method("DELETE")
	 * @OpenApi("
	 *   summary: Deletes the component
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="component", type="string", description="Component name")
	 * })
	 * @Responses({
	 *      @Response(code="200", description="Success"),
	 *      @Response(code="404", description="Not found"),
	 *      @Response(code="500", description="Server error")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function deleteComponent(ApiRequest $request, ApiResponse $response): ApiResponse {
		$component = urldecode($request->getParameter('component'));
		$id = $this->componentManager->getId($component);
		if ($id === null) {
			return $response->withStatus(404, 'Unknown component');
		}
		try {
			$this->componentManager->delete($id);
			return $response;
		} catch (JsonException $e) {
			return $response->withStatus(500, 'Invalid JSON syntax');
		}
	}

	/**
	 * @Path("/{component}")
	 * @Method("PUT")
	 * @OpenApi("
	 *   summary: Edits the component
	 *   requestBody:
	 *     required: true
	 *     content:
	 *      application/json:
	 *          schema:
	 *              type: string
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="component", type="string", description="Component name")
	 * })
	 * @Responses({
	 *      @Response(code="200", description="Success"),
	 *      @Response(code="400", description="Bad request")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function editComponent(ApiRequest $request, ApiResponse $response): ApiResponse {
		$component = urldecode($request->getParameter('component'));
		$id = $this->componentManager->getId($component);
		if ($id === null) {
			return $response->withStatus(400, 'Unknown component name');
		}
		try {
			$this->componentManager->save($request->getJsonBody(), $id);
		} catch (JsonException $e) {
			return $response->withStatus(400, 'Invalid JSON syntax');
		}
		return $response;
	}

	/**
	 * @Path("/{component}")
	 * @Method("GET")
	 * @OpenApi("
	 *   summary: Returns the component configuration and instances of the component
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="component", type="string", description="Component name")
	 * })
	 * @Responses({
	 *      @Response(code="200", description="Success"),
	 *      @Response(code="404", description="Not found")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function getComponent(ApiRequest $request, ApiResponse $response): ApiResponse {
		$component = urldecode($request->getParameter('component'));
		try {
			$this->manager->setComponent($component);
		} catch (NonExistingJsonSchemaException $e) {
			return $response->withStatus(404);
		}
		$id = $this->componentManager->getId($component);
		if ($id === null) {
			return $response->withStatus(404, 'Component not found');
		}
		try {
			$body = [
				'configuration' => $this->componentManager->load($id),
				'instances' => $this->manager->list(),
			];
			return $response->writeJsonBody($body);
		} catch (JsonException $e) {
			return $response->withStatus(500, 'Invalid JSON syntax');
		}
	}

	/**
	 * @Path("/{component}")
	 * @Method("POST")
	 * @OpenApi("
	 *   summary: Creates instance configuration
	 *   requestBody:
	 *     required: true
	 *     content:
	 *      application/json:
	 *          schema:
	 *              type: string
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="component", type="string", description="Component name")
	 * })
	 * @Responses({
	 *     @Response(code="201", description="Created"),
	 *     @Response(code="400", description="Bad request"),
	 *     @Response(code="404", description="Not found")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function createInstance(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			$json = $request->getJsonBody(true);
			$component = urldecode($request->getParameter('component'));
			$this->manager->setComponent($component);
			if (!isset($json['instance'])) {
				return $response->withStatus(400, 'Missing instance name');
			}
			$fileName = $this->manager->getInstanceFileName($json['instance']);
			if ($fileName !== null) {
				return $response->withStatus(400, 'Instance already exits');
			} else {
				$fileName = $this->manager->generateFileName($json);
			}
			$this->manager->save($json, $fileName);
			return $response->withStatus(201);
		} catch (NonExistingJsonSchemaException $e) {
			return $response->withStatus(404, 'Component not found');
		} catch (JsonException $e) {
			return $response->withStatus(400, 'Invalid JSON syntax');
		} catch (InvalidJsonException $e) {
			return $response->withStatus(400, $e->getMessage());
		}
	}

	/**
	 * @Path("/{component}/{instance}")
	 * @Method("DELETE")
	 * @OpenApi("
	 *   summary: Deletes instance configuration by name
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="component", type="string", description="Component name"),
	 *      @RequestParameter(name="instance", type="string", description="Instance name")
	 * })
	 * @Responses({
	 *      @Response(code="200", description="Success"),
	 *      @Response(code="404", description="Not found")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function deleteInstance(ApiRequest $request, ApiResponse $response): ApiResponse {
		$component = urldecode($request->getParameter('component'));
		$this->manager->setComponent($component);
		$instance = urldecode($request->getParameter('instance'));
		$fileName = $this->manager->getInstanceFileName($instance);
		if ($fileName === null) {
			return $response->withStatus(404, 'Component not found');
		}
		$this->manager->deleteFile($fileName);
		return $response;
	}

	/**
	 * @Path("/{component}/{instance}")
	 * @Method("PUT")
	 * @OpenApi("
	 *   summary: Edits instance configuration by name
	 *   requestBody:
	 *     required: true
	 *     content:
	 *      application/json:
	 *          schema:
	 *              type: string
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="component", type="string", description="Component name"),
	 *      @RequestParameter(name="instance", type="string", description="Instance name")
	 * })
	 * @Responses({
	 *      @Response(code="200", description="Success"),
	 *      @Response(code="400", description="Bad request"),
	 *      @Response(code="404", description="Not found")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function editInstance(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			$json = $request->getJsonBody(true);
			$component = urldecode($request->getParameter('component'));
			$this->manager->setComponent($component);
			if (!isset($json['instance'])) {
				return $response->withStatus(400, 'Missing instance name');
			}
			$instance = urldecode($request->getParameter('instance'));
			$fileName = $this->manager->getInstanceFileName($instance);
			$this->manager->save($json, $fileName);
		} catch (NonExistingJsonSchemaException $e) {
			return $response->withStatus(404, 'Component not found');
		} catch (JsonException $e) {
			return $response->withStatus(400, 'Invalid JSON syntax');
		} catch (InvalidJsonException $e) {
			return $response->withStatus(400, $e->getMessage());
		}
		return $response;
	}

	/**
	 * @Path("/{component}/{instance}")
	 * @Method("GET")
	 * @OpenApi("
	 *   summary: Finds instance configuration by name
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="component", type="string", description="Component name"),
	 *      @RequestParameter(name="instance", type="string", description="Instance name")
	 * })
	 * @Responses({
	 *      @Response(code="200", description="Success"),
	 *      @Response(code="404", description="Not found"),
	 *      @Response(code="500", description="Server error")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function getInstance(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			$component = urldecode($request->getParameter('component'));
			$this->manager->setComponent($component);
			$instance = urldecode($request->getParameter('instance'));
			$configuration = $this->manager->loadInstance($instance);
		} catch (NonExistingJsonSchemaException $e) {
			return $response->withStatus(404, 'Component not found');
		} catch (JsonException $e) {
			return $response->withStatus(500, 'Invalid JSON syntax');
		}
		if ($configuration === []) {
			return $response->withStatus(404, 'Instance not found');
		}
		return $response->writeJsonBody($configuration);
	}

}
