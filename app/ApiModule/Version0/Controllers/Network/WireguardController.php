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

namespace App\ApiModule\Version0\Controllers\Network;

use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\OpenApi;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Exception\Api\ClientErrorException;
use Apitte\Core\Exception\Api\ServerErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Controllers\NetworkController;
use App\ApiModule\Version0\Models\RestApiSchemaValidator;
use App\NetworkModule\Exceptions\InterfaceExistsException;
use App\NetworkModule\Exceptions\IpKernelException;
use App\NetworkModule\Exceptions\IpSyntaxException;
use App\NetworkModule\Exceptions\WireguardKeyErrorException;
use App\NetworkModule\Exceptions\WireguardKeyMismatchException;
use App\NetworkModule\Models\WireguardManager;
use App\ServiceModule\Exceptions\NonexistentServiceException;
use App\ServiceModule\Exceptions\UnsupportedInitSystemException;

/**
 * Wireguard VPN controller
 * @Path("/wireguard")
 */
class WireguardController extends NetworkController {

	/**
	 * @var WireguardManager Wireguard VPN manager
	 */
	private $wireguardManager;

	/**
	 * Constructor
	 * @param WireguardManager $wireguardManager Wireguard VPN manager
	 * @param RestApiSchemaValidator $validator REST API JSON schema validator
	 */
	public function __construct(WireguardManager $wireguardManager, RestApiSchemaValidator $validator) {
		$this->wireguardManager = $wireguardManager;
		parent::__construct($validator);
	}

	/**
	 * @Path("/")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: Lists all existing Wireguard VPN tunnels
	 *  responses:
	 *      '200':
	 *          description: Success
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function list(ApiRequest $request, ApiResponse $response): ApiResponse {
		$tunnels = $this->wireguardManager->listTunnels();
		return $response->writeJsonBody($tunnels);
	}

	/**
	 * @Path("/")
	 * @Method("POST")
	 * @OpenApi("
	 *  summary: Creates a new Wireguard VPN tunnel
	 *  requestBody:
	 *      description: Wireguard tunnel configuration
	 *      required: true
	 *      content:
	 *          application/json:
	 *              schema:
	 *                  $ref: '#/components/schemas/WireguardTunnel'
	 *  responses:
	 *      '200':
	 *          description: Success
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function create(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validator->validateRequest('wireguardTunnel', $request);
		try {
			$this->wireguardManager->createTunnel($request->getJsonBody(false));
			return $response->writeBody('Workaround');
		} catch (InterfaceExistsException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S400_BAD_REQUEST, $e);
		} catch (WireguardKeyMismatchException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S400_BAD_REQUEST, $e);
		} catch (IpSyntaxException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		} catch (IpKernelException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	/**
	 * @Path("/state")
	 * @Method("POST")
	 * @OpenApi("
	 *  summary: Change state of a Wireguard tunnel
	 *  requestBody:
	 *      required: true
	 *      content:
	 *          schema:
	 *              application/json:
	 *                  $ref: '#/components/WireguardTunnelState'
	 *  responses:
	 *      '200':
	 *          description: Success
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function changeState(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validator->validateRequest('wireguardTunnelState', $request);
		try {
			$config = $request->getJsonBody(false);
			$this->wireguardManager->changeTunnelState($config);
			return $response->writeBody('Workaround');
		} catch (UnsupportedInitSystemException $e) {
			throw new ServerErrorException('Unsupported init system', ApiResponse::S500_INTERNAL_SERVER_ERROR);
		} catch (NonexistentServiceException $e) {
			throw new ClientErrorException('Service not found', ApiResponse::S404_NOT_FOUND);
		}
	}

	/**
	 * @Path("/keypair")
	 * @Method("POST")
	 * @OpenApi("
	 *  summary: Generates a new Wireguard key pair
	 *  responses:
	 *      '200':
	 *          description: Success
	 *      '500':
	 *          $ref: '#/components/responses/ServerError'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function generateKeys(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			$result = $this->wireguardManager->generateKeys();
			return $response->writeJsonBody($result);
		} catch (WireguardKeyErrorException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

}
