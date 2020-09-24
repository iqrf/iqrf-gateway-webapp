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
use Apitte\Core\Exception\Api\ServerErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ServiceModule\Exceptions\NonexistentServiceException;
use App\ServiceModule\Exceptions\NotImplementedException;
use App\ServiceModule\Exceptions\UnsupportedInitSystemException;
use App\ServiceModule\Models\ServiceManager;
use function GuzzleHttp\Psr7\stream_for;

/**
 * Service manager controller
 * @Path("/services")
 * @Tag("Service manager")
 */
class ServicesController extends BaseController {

	/**
	 * @var ServiceManager Service manager
	 */
	private $manager;

	/**
	 * Whitelisted services
	 */
	private const WHITELISTED_SERVICES = [
		'gwman-client',
		'iqrf-gateway-controller',
		'iqrf-gateway-daemon',
		'iqrf-gateway-translator',
		'ssh',
		'unattended-upgrades',
	];

	/**
	 * Constructor
	 * @param ServiceManager $manager Service manager
	 */
	public function __construct(ServiceManager $manager) {
		$this->manager = $manager;
	}

	/**
	 * @Path("/")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: Returns the supported services
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/ServiceList'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function listServices(ApiRequest $request, ApiResponse $response): ApiResponse {
		$services = ['services' => self::WHITELISTED_SERVICES];
		return $response->writeJsonBody($services);
	}

	/**
	 * @Path("/{name}")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: Returns the service status
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/ServiceStatus'
	 *      '400':
	 *          $ref: '#/components/responses/BadRequest'
	 *      '404':
	 *          description: Not found
	 *      '500':
	 *          description: Unsupported init system
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="name", type="string", description="Service name")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function getService(ApiRequest $request, ApiResponse $response): ApiResponse {
		$name = $request->getParameter('name');
		$this->isServiceWhitelisted($name);
		$status = [];
		try {
			try {
				$status['enabled'] = $this->manager->isEnabled($name);
				$status['active'] = $this->manager->isActive($name);
			} catch (NotImplementedException $e) {
				unset($status['enabled'], $status['active']);
			}
			$status['status'] = $this->manager->getStatus($name);
			return $response->writeJsonBody($status);
		} catch (UnsupportedInitSystemException $e) {
			throw new ServerErrorException('Unsupported init system', ApiResponse::S500_INTERNAL_SERVER_ERROR);
		} catch (NonexistentServiceException $e) {
			throw new ClientErrorException('Service not found', ApiResponse::S404_NOT_FOUND);
		}
	}

	/**
	 * @Path("/{name}/enable")
	 * @Method("POST")
	 * @OpenApi("
	 *  summary: Enables the service
	 *  responses:
	 *      '200':
	 *          description: Success
	 *      '400':
	 *          $ref: '#/components/responses/BadRequest'
	 *      '404':
	 *          description: Not found
	 *      '500':
	 *          description: Unsupported init system
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="name", type="string", description="Service name")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function enableService(ApiRequest $request, ApiResponse $response): ApiResponse {
		$name = $request->getParameter('name');
		$this->isServiceWhitelisted($name);
		try {
			$this->manager->enable($name);
			return $response->withBody(stream_for());
		} catch (UnsupportedInitSystemException $e) {
			throw new ServerErrorException('Unsupported init system', ApiResponse::S500_INTERNAL_SERVER_ERROR);
		} catch (NonexistentServiceException $e) {
			throw new ClientErrorException('Service not found', ApiResponse::S404_NOT_FOUND);
		}
	}

	/**
	 * @Path("/{name}/disable")
	 * @Method("POST")
	 * @OpenApi("
	 *  summary: Disables the service
	 *  responses:
	 *      '200':
	 *          description: Success
	 *      '400':
	 *          $ref: '#/components/responses/BadRequest'
	 *      '404':
	 *          description: Not found
	 *      '500':
	 *          description: Unsupported init system
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="name", type="string", description="Service name")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function disableService(ApiRequest $request, ApiResponse $response): ApiResponse {
		$name = $request->getParameter('name');
		$this->isServiceWhitelisted($name);
		try {
			$this->manager->disable($name);
			return $response->withBody(stream_for());
		} catch (UnsupportedInitSystemException $e) {
			throw new ServerErrorException('Unsupported init system', ApiResponse::S500_INTERNAL_SERVER_ERROR);
		} catch (NonexistentServiceException $e) {
			throw new ClientErrorException('Service not found', ApiResponse::S404_NOT_FOUND);
		}
	}

	/**
	 * @Path("/{name}/start")
	 * @Method("POST")
	 * @OpenApi("
	 *  summary: Starts the service
	 *  responses:
	 *      '200':
	 *          description: Success
	 *      '400':
	 *          $ref: '#/components/responses/BadRequest'
	 *      '404':
	 *          description: Not found
	 *      '500':
	 *          description: Unsupported init system
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="name", type="string", description="Service name")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function startService(ApiRequest $request, ApiResponse $response): ApiResponse {
		$name = $request->getParameter('name');
		$this->isServiceWhitelisted($name);
		try {
			$this->manager->start($name);
			return $response->withBody(stream_for());
		} catch (UnsupportedInitSystemException $e) {
			throw new ServerErrorException('Unsupported init system', ApiResponse::S500_INTERNAL_SERVER_ERROR);
		} catch (NonexistentServiceException $e) {
			throw new ClientErrorException('Service not found', ApiResponse::S404_NOT_FOUND);
		}
	}

	/**
	 * @Path("/{name}/stop")
	 * @Method("POST")
	 * @OpenApi("
	 *  summary: Stops the service
	 *  responses:
	 *      '200':
	 *          description: Success
	 *      '400':
	 *          $ref: '#/components/responses/BadRequest'
	 *      '404':
	 *          description: Not found
	 *      '500':
	 *          description: Unsupported init system
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="name", type="string", description="Service name")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function stopService(ApiRequest $request, ApiResponse $response): ApiResponse {
		$name = $request->getParameter('name');
		$this->isServiceWhitelisted($name);
		try {
			$this->manager->stop($name);
			return $response->withBody(stream_for());
		} catch (UnsupportedInitSystemException $e) {
			throw new ServerErrorException('Unsupported init system', ApiResponse::S500_INTERNAL_SERVER_ERROR);
		} catch (NonexistentServiceException $e) {
			throw new ClientErrorException('Service not found', ApiResponse::S404_NOT_FOUND);
		}
	}

	/**
	 * @Path("/{name}/restart")
	 * @Method("POST")
	 * @OpenApi("
	 *  summary: Restarts the service
	 *  responses:
	 *      '200':
	 *          description: Success
	 *      '400':
	 *          $ref: '#/components/responses/BadRequest'
	 *      '404':
	 *          description: Service not found
	 *      '500':
	 *          description: Unsupported init system
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="name", type="string", description="Service name")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function restartService(ApiRequest $request, ApiResponse $response): ApiResponse {
		$name = $request->getParameter('name');
		$this->isServiceWhitelisted($name);
		try {
			$this->manager->restart($name);
			return $response->withBody(stream_for());
		} catch (UnsupportedInitSystemException $e) {
			throw new ServerErrorException('Unsupported init system', ApiResponse::S500_INTERNAL_SERVER_ERROR);
		} catch (NonexistentServiceException $e) {
			throw new ClientErrorException('Service not found', ApiResponse::S404_NOT_FOUND);
		}
	}

	/**
	 * Checks if the service is whitelisted
	 * @param string $name Service name
	 */
	private function isServiceWhitelisted(string $name): void {
		if (!in_array($name, self::WHITELISTED_SERVICES, true)) {
			throw new ClientErrorException('Unsupported service', ApiResponse::S400_BAD_REQUEST);
		}
	}

}
