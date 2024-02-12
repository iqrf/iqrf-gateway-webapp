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

namespace App\ApiModule\Version0\Controllers\Network;

use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\OpenApi;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Annotation\Controller\RequestParameter;
use Apitte\Core\Exception\Api\ClientErrorException;
use Apitte\Core\Exception\Api\ServerErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Controllers\NetworkController;
use App\ApiModule\Version0\Models\RestApiSchemaValidator;
use App\Models\Database\Entities\WireguardInterface;
use App\NetworkModule\Exceptions\InterfaceExistsException;
use App\NetworkModule\Exceptions\NonexistentWireguardTunnelException;
use App\NetworkModule\Exceptions\WireguardInvalidEndpointException;
use App\NetworkModule\Exceptions\WireguardKeyErrorException;
use App\NetworkModule\Models\WireguardManager;
use App\ServiceModule\Exceptions\NonexistentServiceException;
use App\ServiceModule\Exceptions\UnsupportedInitSystemException;
use App\ServiceModule\Models\ServiceManager;

/**
 * WireGuard VPN controller
 */
#[Path('/wireguard')]
class WireGuardController extends NetworkController {

	/**
	 * Constructor
	 * @param ServiceManager $serviceManager Service manager
	 * @param WireguardManager $manager WireGuard VPN manager
	 * @param RestApiSchemaValidator $validator REST API JSON schema validator
	 */
	public function __construct(
		private readonly ServiceManager $serviceManager,
		private readonly WireguardManager $manager,
		RestApiSchemaValidator $validator,
	) {
		parent::__construct($validator);
	}

	#[Path('/')]
	#[Method('GET')]
	#[OpenApi('
		summary: Lists all existing WireGuard VPN tunnels
		responses:
			\'200\':
				description: Success
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
	')]
	public function list(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['network']);
		$tunnels = $this->manager->listInterfaces();
		return $response->writeJsonBody($tunnels);
	}
	#[Path('/{id}')]
	#[Method('GET')]
	#[OpenApi('
		summary: Retrieves configuration of WireGuard tunnel
		responses:
			\'200\':
				description: Success
				content:
					application/json:
						schema:
							$ref: \'#/components/schemas/WireguardTunnel\'
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
			\'404\':
				$ref: \'#/components/responses/NotFound\'
			\'500\':
				$ref: \'#/components/responses/ServerError\'
	')]
	#[RequestParameter(name: 'id', type: 'integer', description: 'WireGuard tunnel ID')]
	public function get(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['network']);
		try {
			$id = (int) $request->getParameter('id');
			$tunnel = $this->manager->getInterface($id)->jsonSerialize();
			$tunnel['publicKey'] = $this->manager->generatePublicKey($tunnel['privateKey']);
			return $response->writeJsonBody($tunnel);
		} catch (NonexistentWireguardTunnelException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S404_NOT_FOUND);
		} catch (WireguardKeyErrorException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	#[Path('/')]
	#[Method('POST')]
	#[OpenApi('
		summary: Creates a new WireGuard VPN tunnel
		requestBody:
			description: WireGuard tunnel configuration
			required: true
			content:
				application/json:
					schema:
						$ref: \'#/components/schemas/WireguardTunnel\'
		responses:
			\'200\':
				description: Success
			\'400\':
				$ref: \'#/components/responses/BadRequest\'
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
			\'500\':
				$ref: \'#/components/responses/ServerError\'
	')]
	public function create(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['network']);
		$this->validator->validateRequest('wireguardTunnel', $request);
		try {
			$this->manager->createInterface($request->getJsonBody(false));
			return $response->writeBody('Workaround');
		} catch (InterfaceExistsException | WireguardInvalidEndpointException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S400_BAD_REQUEST);
		} catch (WireguardKeyErrorException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	#[Path('/{id}')]
	#[Method('PUT')]
	#[OpenApi('
		summary: Edits an existing WireGuard VPN tunnel
		requestBody:
			description: WireGuard tunnel configuration
			required: true
			content:
				application/json:
					schema:
						$ref: \'#/components/schemas/WireguardTunnel\'
		responses:
			\'200\':
				description: Success
			\'400\':
				$ref: \'#/components/responses/BadRequest\'
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
			\'404\':
				$ref: \'#/components/responses/NotFound\'
			\'500\':
				$ref: \'#/components/responses/ServerError\'
	')]
	#[RequestParameter(name: 'id', type: 'integer', description: 'WireGuard tunnel ID')]
	public function edit(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['network']);
		$this->validator->validateRequest('wireguardTunnel', $request);
		try {
			$id = (int) $request->getParameter('id');
			$this->manager->editInterface($id, $request->getJsonBody(false));
			return $response->writeBody('Workaround');
		} catch (NonexistentWireguardTunnelException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S404_NOT_FOUND);
		} catch (InterfaceExistsException | WireguardInvalidEndpointException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S400_BAD_REQUEST);
		} catch (WireguardKeyErrorException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	#[Path('/{id}')]
	#[Method('DELETE')]
	#[OpenApi('
		summary: Removes an existing WireGuard VPN tunnel
		responses:
			\'200\':
				description: Success
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
			\'404\':
				$ref: \'#/components/responses/NotFound\'
			\'500\':
				$ref: \'#/components/responses/ServerError\'
	')]
	#[RequestParameter(name: 'id', type: 'integer', description: 'WireGuard tunnel ID')]
	public function remove(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['network']);
		try {
			$id = (int) $request->getParameter('id');
			$service = $this->tunnelService($this->manager->getInterface($id));
			if ($this->serviceManager->isActive($service) || $this->serviceManager->isEnabled($service)) {
				$this->serviceManager->disable($service);
			}
			$this->manager->removeInterface($id);
			return $response->writeBody('Workaround');
		} catch (NonexistentWireguardTunnelException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S404_NOT_FOUND, $e);
		} catch (NonexistentServiceException $e) {
			throw new ClientErrorException('Wireguard tunnel not found', ApiResponse::S404_NOT_FOUND);
		} catch (UnsupportedInitSystemException $e) {
			throw new ServerErrorException('Unsupported init system', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	#[Path('/{id}/activate')]
	#[Method('POST')]
	#[OpenApi('
		summary: Activates WireGuard VPN tunnel
		responses:
			\'200\':
				description: Success
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
			\'404\':
				$ref: \'#/components/responses/NotFound\'
			\'500\':
				$ref: \'#/components/responses/ServerError\'
	')]
	#[RequestParameter(name: 'id', type: 'integer', description: 'WireGuard tunnel ID')]
	public function activate(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['network']);
		try {
			$tunnel = $this->manager->getInterface((int) $request->getParameter('id'));
			$this->serviceManager->start($this->tunnelService($tunnel));
			return $response->writeBody('Workaround');
		} catch (NonexistentWireguardTunnelException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S404_NOT_FOUND);
		} catch (NonexistentServiceException $e) {
			throw new ClientErrorException('Wireguard tunnel not found', ApiResponse::S404_NOT_FOUND);
		} catch (UnsupportedInitSystemException $e) {
			throw new ServerErrorException('Unsupported init system', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	#[Path('/{id}/deactivate')]
	#[Method('POST')]
	#[OpenApi('
		summary: Deactivates WireGuard VPN tunnel
		responses:
			\'200\':
				description: Success
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
			\'404\':
				$ref: \'#/components/responses/NotFound\'
			\'500\':
				$ref: \'#/components/responses/ServerError\'
	')]
	#[RequestParameter(name: 'id', type: 'integer', description: 'WireGuard tunnel ID')]
	public function deactivate(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['network']);
		try {
			$tunnel = $this->manager->getInterface((int) $request->getParameter('id'));
			$this->serviceManager->stop($this->tunnelService($tunnel));
			return $response->writeBody('Workaround');
		} catch (NonexistentWireguardTunnelException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S404_NOT_FOUND);
		} catch (NonexistentServiceException $e) {
			throw new ClientErrorException('Wireguard tunnel not found', ApiResponse::S404_NOT_FOUND);
		} catch (UnsupportedInitSystemException $e) {
			throw new ServerErrorException('Unsupported init system', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	#[Path('/{id}/enable')]
	#[Method('POST')]
	#[OpenApi('
		summary: Enables WireGuard VPN tunnel activation on startup
		responses:
			\'200\':
				description: Success
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
			\'404\':
				$ref: \'#/components/responses/NotFound\'
			\'500\':
				$ref: \'#/components/responses/ServerError\'
	')]
	#[RequestParameter(name: 'id', type: 'integer', description: 'WireGuard tunnel ID')]
	public function enable(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['network']);
		try {
			$tunnel = $this->manager->getInterface((int) $request->getParameter('id'));
			$this->serviceManager->enable($this->tunnelService($tunnel));
			return $response->writeBody('Workaround');
		} catch (NonexistentWireguardTunnelException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S404_NOT_FOUND);
		} catch (NonexistentServiceException $e) {
			throw new ClientErrorException('Wireguard tunnel not found', ApiResponse::S404_NOT_FOUND);
		} catch (UnsupportedInitSystemException $e) {
			throw new ServerErrorException('Unsupported init system', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	#[Path('/{id}/disable')]
	#[Method('POST')]
	#[OpenApi('
		summary: Disables WireGuard VPN tunnel activation on startup
		responses:
			\'200\':
				description: Success
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
			\'404\':
				$ref: \'#/components/responses/NotFound\'
			\'500\':
				$ref: \'#/components/responses/ServerError\'
	')]
	#[RequestParameter(name: 'id', type: 'integer', description: 'WireGuard tunnel ID')]
	public function disable(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['network']);
		try {
			$tunnel = $this->manager->getInterface((int) $request->getParameter('id'));
			$this->serviceManager->disable($this->tunnelService($tunnel));
			return $response->writeBody('Workaround');
		} catch (NonexistentWireguardTunnelException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S404_NOT_FOUND);
		} catch (NonexistentServiceException $e) {
			throw new ClientErrorException('Wireguard tunnel not found', ApiResponse::S404_NOT_FOUND);
		} catch (UnsupportedInitSystemException $e) {
			throw new ServerErrorException('Unsupported init system', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	#[Path('/keypair')]
	#[Method('POST')]
	#[OpenApi('
		summary: Generates a new WireGuard key pair
		responses:
			\'200\':
				description: Success
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
			\'500\':
				$ref: \'#/components/responses/ServerError\'
	 ')]
	public function generateKeys(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['network']);
		try {
			$result = $this->manager->generateKeys();
			return $response->writeJsonBody($result);
		} catch (WireguardKeyErrorException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	/**
	 * Constructs WireGuard tunnel service name
	 * @param WireguardInterface $iface WireGuard interface entity
	 */
	private function tunnelService(WireguardInterface $iface): string {
		return 'iqrf-gateway-webapp-wg@' . $iface->getName();
	}

}
