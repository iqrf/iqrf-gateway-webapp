<?php

/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
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
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Exception\Api\ClientErrorException;
use Apitte\Core\Exception\Api\ServerErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Models\ControllerValidators;
use App\CoreModule\Models\FeatureManager;
use App\ServiceModule\Exceptions\NonexistentServiceException;
use App\ServiceModule\Exceptions\NotImplementedException;
use App\ServiceModule\Exceptions\UnsupportedInitSystemException;
use App\ServiceModule\Models\ServiceManager;

/**
 * Service manager controller
 */
#[Path('/services')]
#[Tag('Service manager')]
class ServicesController extends BaseController {

	/**
	 * Whitelisted services
	 */
	private const WHITELISTED_SERVICES = [
		'apcupsd' => 'apcupsd',
		'influxdb' => 'iqaros',
		'iqaros-data-availability' => 'iqaros',
		'iqaros-influxdb-poll' => 'iqaros',
		'iqaros-network-sync' => 'iqaros',
		'iqaros-webapp-wsserver' => 'iqaros',
		'iqrf-cloud-provisioning' => 'iqrfCloudProvisioning',
		'iqrf-gateway-controller' => 'iqrfGatewayController',
		'iqrf-gateway-daemon' => null,
		'iqrf-gateway-influxdb-bridge' => 'iqrfGatewayInfluxdbBridge',
		'iqrf-gateway-translator' => 'iqrfGatewayTranslator',
		'mender-connect' => 'mender',
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
	 * @param ControllerValidators $validators Controller validators
	 */
	public function __construct(
		private readonly ServiceManager $manager,
		private readonly FeatureManager $featureManager,
		ControllerValidators $validators,
	) {
		parent::__construct($validators);
	}

	#[Path('/')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Returns the supported services
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/ServiceList'
	EOT)]
	#[RequestParameter(name: 'withStatus', type: 'bool', in: 'query', description: 'Include service status')]
	public function listServices(ApiRequest $request, ApiResponse $response): ApiResponse {
		$array = [];
		foreach ($this->getWhitelistedServices() as $service) {
			try {
				$array[] = $this->manager->getState($service, boolval($request->getQueryParam('withStatus', false)));
			} catch (UnsupportedInitSystemException $e) {
				throw new ServerErrorException('Unsupported init system', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
			} catch (NonexistentServiceException) {
				continue;
			}
		}
		$response = $response->writeJsonBody($array);
		return $this->validators->validateResponse('serviceList', $response);
	}

	#[Path('/{name}')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Returns the service status
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/ServiceStatus'
			'400':
				$ref: '#/components/responses/BadRequest'
			'404':
				$ref: '#/components/responses/NotFound'
			'500':
				$ref: '#/components/responses/UnsupportedInitSystem'
	EOT)]
	#[RequestParameter(name: 'name', type: 'string', description: 'Service name')]
	public function getService(ApiRequest $request, ApiResponse $response): ApiResponse {
		$name = $request->getParameter('name');
		$this->isServiceWhitelisted($name);
		/** @var array{active:bool, enabled: bool} $status */
		$status = [];
		try {
			try {
				$status['active'] = $this->manager->isActive($name);
				$status['enabled'] = $this->manager->isEnabled($name);
			} catch (NotImplementedException) {
				unset($status['active'], $status['enabled']);
			}
			$status['status'] = $this->manager->getStatus($name);
			$response = $response->writeJsonBody($status);
			return $this->validators->validateResponse('serviceStatus', $response);
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

	#[Path('/{name}/enable')]
	#[Method('POST')]
	#[OpenApi(<<<'EOT'
		summary: Enables the service
		requestBody:
			required: false
			content:
				application/json:
					schema:
						$ref: '#/components/schemas/ServiceEnable'
		responses:
			'200':
				description: Success
			'400':
				$ref: '#/components/responses/BadRequest'
			'404':
				$ref: '#/components/responses/NotFound'
			'500':
				$ref: '#/components/responses/UnsupportedInitSystem'
	EOT)]
	#[RequestParameter(name: 'name', type: 'string', description: 'Service name')]
	public function enableService(ApiRequest $request, ApiResponse $response): ApiResponse {
		$name = $request->getParameter('name');
		$this->isServiceWhitelisted($name);
		$start = true;
		if ($request->getContentsCopy() !== '') {
			$this->validators->validateRequest('serviceEnable', $request);
			$start = $request->getJsonBody()['start'];
		}
		try {
			$this->manager->enable($name, $start);
			return $response;
		} catch (UnsupportedInitSystemException $e) {
			throw new ServerErrorException('Unsupported init system', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		} catch (NonexistentServiceException $e) {
			throw new ClientErrorException('Service not found', ApiResponse::S404_NOT_FOUND, $e);
		}
	}

	#[Path('/{name}/disable')]
	#[Method('POST')]
	#[OpenApi(<<<'EOT'
		summary: Disables the service
		requestBody:
			required: false
			content:
				application/json:
					schema:
						$ref: '#/components/schemas/ServiceDisable'
		responses:
			'200':
				description: Success
			'400':
				$ref: '#/components/responses/BadRequest'
			'403':
				$ref: '#/components/responses/Forbidden'
			'404':
				$ref: '#/components/responses/NotFound'
			'500':
				$ref: '#/components/responses/UnsupportedInitSystem'
	EOT)]
	#[RequestParameter(name: 'name', type: 'string', description: 'Service name')]
	public function disableService(ApiRequest $request, ApiResponse $response): ApiResponse {
		$name = $request->getParameter('name');
		$this->isServiceWhitelisted($name);
		$stop = true;
		if ($request->getContentsCopy() !== '') {
			$this->validators->validateRequest('serviceDisable', $request);
			$stop = $request->getJsonBody()['stop'];
		}
		try {
			$this->manager->disable($name, $stop);
			return $response;
		} catch (UnsupportedInitSystemException $e) {
			throw new ServerErrorException('Unsupported init system', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		} catch (NonexistentServiceException $e) {
			throw new ClientErrorException('Service not found', ApiResponse::S404_NOT_FOUND, $e);
		}
	}

	#[Path('/{name}/start')]
	#[Method('POST')]
	#[OpenApi(<<<'EOT'
		summary: Starts the service
		responses:
			'200':
				description: Success
			'400':
				$ref: '#/components/responses/BadRequest'
			'403':
				$ref: '#/components/responses/Forbidden'
			'404':
				$ref: '#/components/responses/NotFound'
			'500':
				$ref: '#/components/responses/UnsupportedInitSystem'
	EOT)]
	#[RequestParameter(name: 'name', type: 'string', description: 'Service name')]
	public function startService(ApiRequest $request, ApiResponse $response): ApiResponse {
		$name = $request->getParameter('name');
		$this->isServiceWhitelisted($name);
		try {
			$this->manager->start($name);
			return $response;
		} catch (UnsupportedInitSystemException $e) {
			throw new ServerErrorException('Unsupported init system', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		} catch (NonexistentServiceException $e) {
			throw new ClientErrorException('Service not found', ApiResponse::S404_NOT_FOUND, $e);
		}
	}

	#[Path('/{name}/stop')]
	#[Method('POST')]
	#[OpenApi(<<<'EOT'
		summary: Stops the service
		responses:
			'200':
				description: Success
			'400':
				$ref: '#/components/responses/BadRequest'
			'403':
				$ref: '#/components/responses/Forbidden'
			'404':
				$ref: '#/components/responses/NotFound'
			'500':
				$ref: '#/components/responses/UnsupportedInitSystem'
	EOT)]
	#[RequestParameter(name: 'name', type: 'string', description: 'Service name')]
	public function stopService(ApiRequest $request, ApiResponse $response): ApiResponse {
		$name = $request->getParameter('name');
		$this->isServiceWhitelisted($name);
		try {
			$this->manager->stop($name);
			return $response;
		} catch (UnsupportedInitSystemException $e) {
			throw new ServerErrorException('Unsupported init system', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		} catch (NonexistentServiceException $e) {
			throw new ClientErrorException('Service not found', ApiResponse::S404_NOT_FOUND, $e);
		}
	}

	#[Path('/{name}/restart')]
	#[Method('POST')]
	#[OpenApi(<<<'EOT'
		summary: Restarts the service
		responses:
			'200':
				description: Success
			'400':
				$ref: '#/components/responses/BadRequest'
			'403':
				$ref: '#/components/responses/Forbidden'
			'404':
				$ref: '#/components/responses/NotFound'
			'500':
				$ref: '#/components/responses/UnsupportedInitSystem'
	EOT)]
	#[RequestParameter(name: 'name', type: 'string', description: 'Service name')]
	public function restartService(ApiRequest $request, ApiResponse $response): ApiResponse {
		$name = $request->getParameter('name');
		$this->isServiceWhitelisted($name);
		try {
			$this->manager->restart($name);
			return $response;
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
