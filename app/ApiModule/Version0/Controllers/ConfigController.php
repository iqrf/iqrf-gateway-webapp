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
	 *      @Response(code="404", description="Not found")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function get(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $response->writeJsonBody($this->mainManager->load());
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
	 *      @Response(code="404", description="Not found")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function edit(ApiRequest $request, ApiResponse $response): ApiResponse {
		$json = $request->getJsonBody(true);
		$this->mainManager->save($json);
		return $response;
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
	 *      @Response(code="200", description="Success"),
	 *      @Response(code="404", description="Not found")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function createComponent(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			$json = $request->getJsonBody(true);
			$this->componentManager->add($json);
		} catch (JsonException $e) {
			return $response->withStatus(500);
		}
		return $response;
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
	 *      @Response(code="200", description="Success")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function deleteComponent(ApiRequest $request, ApiResponse $response): ApiResponse {
		$id = $this->componentManager->getId(urldecode($request->getParameter('component')));
		if ($id === null) {
			return $response;
		}
		$this->componentManager->delete($id);
		return $response;
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
	 *      @Response(code="200", description="Success")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function editComponent(ApiRequest $request, ApiResponse $response): ApiResponse {
		$id = $this->componentManager->getId(urldecode($request->getParameter('component')));
		if ($id === null) {
			return $response->withStatus(400, 'Unknown component name');
		}
		$this->componentManager->save($request->getJsonBody(), $id);
		return $response;
	}

	/**
	 * @Path("/{component}")
	 * @Method("GET")
	 * @OpenApi("
	 *   summary: Lists instances of the component
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
	public function listInstances(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			$this->manager->setComponent(urldecode($request->getParameter('component')));
		} catch (NonExistingJsonSchemaException $e) {
			return $response->withStatus(404);
		}
		return $response->writeJsonBody($this->manager->list());
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
	 *      @Response(code="200", description="Success"),
	 *      @Response(code="404", description="Not found")
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
			}
			$this->manager->save($json, $fileName);
		} catch (NonExistingJsonSchemaException $e) {
			return $response->withStatus(404, 'Component not found.');
		} catch (JsonException $e) {
			return $response->withStatus(500);
		}
		return $response;
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
			return $response;
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
			return $response->withStatus(404, 'Component not found.');
		} catch (JsonException $e) {
			return $response->withStatus(500);
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
	 *      @Response(code="404", description="Not found")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function getInstance(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			$this->manager->setComponent(urldecode($request->getParameter('component')));
			$configuration = $this->manager->loadInstance(urldecode($request->getParameter('instance')));
		} catch (NonExistingJsonSchemaException $e) {
			return $response->withStatus(404, 'Component not found.');
		} catch (JsonException $e) {
			return $response->withStatus(500);
		}
		if ($configuration === []) {
			return $response->withStatus(404, 'Instance not found.');
		}
		return $response->writeJsonBody($configuration);
	}

}
