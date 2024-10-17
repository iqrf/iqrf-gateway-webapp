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
use App\ApiModule\Version0\Models\RestApiSchemaValidator;
use App\CoreModule\Models\FeatureManager;
use App\ServiceModule\Exceptions\NonexistentServiceException;
use App\ServiceModule\Exceptions\NotImplementedException;
use App\ServiceModule\Exceptions\UnsupportedInitSystemException;
use App\ServiceModule\Models\ServiceManager;

/**
 * Service manager controller
 * @Path("/services")
 * @Tag("Service manager")
 */
class ServicesController extends BaseController {

	/**
	 * @var FeatureManager Optional features manager
	 */
	private FeatureManager $featureManager;

	/**
	 * @var ServiceManager Service manager
	 */
	private ServiceManager $manager;

	/**
	 * @var array<string, string|null> Whitelisted services
	 */
	private const WHITELISTED_SERVICES = [
		'apcupsd' => 'apcupsd',
		'iqrf-gateway-controller' => 'iqrfGatewayController',
		'iqrf-gateway-daemon' => null,
		'iqrf-gateway-translator' => 'iqrfGatewayTranslator',
		'mender-client' => 'mender',
		'mender-updated' => 'mender',
		'monit' => 'monit',
		'ModemManager' => 'networkManager',
		'nodered' => 'nodeRed',
		'ssh' => 'ssh',
		'systemd-journald' => 'journal',
		'unattended-upgrades' => 'unattendedUpgrades',
	];

	/**
	 * Constructor
	 * @param ServiceManager $manager Service manager
	 * @param FeatureManager $featureManager Optional features manager
	 * @param RestApiSchemaValidator $validator REST API JSON schema validator
	 */
	public function __construct(ServiceManager $manager, FeatureManager $featureManager, RestApiSchemaValidator $validator) {
		$this->manager = $manager;
		$this->featureManager = $featureManager;
		parent::__construct($validator);
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
		$services = ['services' => $this->getWhitelistedServices()];
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
		/** @var array{active:bool, enabled: bool} $status */
		$status = [];
		try {
			try {
				$status['active'] = $this->manager->isActive($name);
				$status['enabled'] = $this->manager->isEnabled($name);
			} catch (NotImplementedException $e) {
				unset($status['active'], $status['enabled']);
			}
			$status['status'] = $this->manager->getStatus($name);
			return $response->writeJsonBody($status);
		} catch (UnsupportedInitSystemException $e) {
			throw new ServerErrorException('Unsupported init system', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		} catch (NonexistentServiceException $e) {
			throw new ClientErrorException('Service not found', ApiResponse::S404_NOT_FOUND, $e);
		}
	}

	/**
	 * Returns the whitelisted services
	 * @return array<string> Whitelisted services
	 */
	public function getWhitelistedServices(): array {
		return array_keys(array_filter(self::WHITELISTED_SERVICES, function (?string $feature): bool {
			if ($feature === null) {
				return true;
			}
			$features = $this->featureManager->listEnabled();
			return in_array($feature, $features, true);
		}));
	}

	/**
	 * @Path("/{name}/enable")
	 * @Method("POST")
	 * @OpenApi("
	 *  summary: Enables the service
	 *  requestBody:
	 *      required: false
	 *      content:
	 *          application/json:
	 *              schema:
	 *                  $ref: '#/components/schemas/ServiceEnable'
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
		$start = true;
		if ($request->getContentsCopy() !== '') {
			$this->validator->validateRequest('serviceEnable', $request);
			$start = $request->getJsonBody()['start'];
		}
		try {
			if ($name === 'mender-client') {
				$this->manager->enable('mender-connect');
			}
			$this->manager->enable($name, $start);
			return $response->writeBody('Workaround');
		} catch (UnsupportedInitSystemException $e) {
			throw new ServerErrorException('Unsupported init system', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		} catch (NonexistentServiceException $e) {
			throw new ClientErrorException('Service not found', ApiResponse::S404_NOT_FOUND, $e);
		}
	}

	/**
	 * @Path("/{name}/disable")
	 * @Method("POST")
	 * @OpenApi("
	 *  summary: Disables the service
	 *  requestBody:
	 *      required: false
	 *      content:
	 *          application/json:
	 *              schema:
	 *                  $ref: '#/components/schemas/ServiceDisable'
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
		$stop = true;
		if ($request->getContentsCopy() !== '') {
			$this->validator->validateRequest('serviceDisable', $request);
			$stop = $request->getJsonBody()['stop'];
		}
		try {
			if ($name === 'mender-client') {
				$this->manager->disable('mender-connect');
			}
			$this->manager->disable($name, $stop);
			return $response->writeBody('Workaround');
		} catch (UnsupportedInitSystemException $e) {
			throw new ServerErrorException('Unsupported init system', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		} catch (NonexistentServiceException $e) {
			throw new ClientErrorException('Service not found', ApiResponse::S404_NOT_FOUND, $e);
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
			return $response->writeBody('Workaround');
		} catch (UnsupportedInitSystemException $e) {
			throw new ServerErrorException('Unsupported init system', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		} catch (NonexistentServiceException $e) {
			throw new ClientErrorException('Service not found', ApiResponse::S404_NOT_FOUND, $e);
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
			return $response->writeBody('Workaround');
		} catch (UnsupportedInitSystemException $e) {
			throw new ServerErrorException('Unsupported init system', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		} catch (NonexistentServiceException $e) {
			throw new ClientErrorException('Service not found', ApiResponse::S404_NOT_FOUND, $e);
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
			return $response->writeBody('Workaround');
		} catch (UnsupportedInitSystemException $e) {
			throw new ServerErrorException('Unsupported init system', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		} catch (NonexistentServiceException $e) {
			throw new ClientErrorException('Service not found', ApiResponse::S404_NOT_FOUND, $e);
		}
	}

	/**
	 * Checks if the service is whitelisted
	 * @param string $name Service name
	 */
	private function isServiceWhitelisted(string $name): void {
		if (!in_array($name, $this->getWhitelistedServices(), true)) {
			throw new ClientErrorException('Unsupported service', ApiResponse::S400_BAD_REQUEST);
		}
	}

}
