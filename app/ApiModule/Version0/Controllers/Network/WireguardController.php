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
use Apitte\Core\Annotation\Controller\RequestParameter;
use Apitte\Core\Annotation\Controller\RequestParameters;
use Apitte\Core\Exception\Api\ClientErrorException;
use Apitte\Core\Exception\Api\ServerErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Controllers\NetworkController;
use App\ApiModule\Version0\Models\RestApiSchemaValidator;
use App\NetworkModule\Exceptions\InterfaceExistsException;
use App\NetworkModule\Exceptions\NonexistentWireguardTunnelException;
use App\NetworkModule\Exceptions\WireguardInvalidEndpointException;
use App\NetworkModule\Exceptions\WireguardKeyErrorException;
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
		$tunnels = $this->wireguardManager->listInterfaces();
		return $response->writeJsonBody($tunnels);
	}

	/**
	 * @Path("/{id}")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: Retrieves configuration of Wireguard tunnel
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              schema:
	 *                  application/json:
	 *                      $ref: '#/components/schemas/WireguardTunnel'
	 *      '404':
	 *          description: Not found
	 *      '500':
	 *          $ref: '#/components/responses/ServerError'
	 * ")
	 * @RequestParameters(
	 *     @RequestParameter(name="id", type="integer", description="Wireguard tunnel id")
	 * )
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function get(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			$id = (int) $request->getParameter('id');
			$tunnel = $this->wireguardManager->getInterface($id)->jsonSerialize();
			$tunnel['publicKey'] = $this->wireguardManager->generatePublicKey($tunnel['privateKey']);
			return $response->writeJsonBody($tunnel);
		} catch (NonexistentWireguardTunnelException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S404_NOT_FOUND, $e);
		} catch (WireguardKeyErrorException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
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
	 *      '400':
	 *          $ref: '#/components/responses/BadRequest'
	 *      '500':
	 *          $ref: '#/components/responses/ServerError'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function create(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validator->validateRequest('wireguardTunnel', $request);
		try {
			$this->wireguardManager->createInterface($request->getJsonBody(false));
			return $response->writeBody('Workaround');
		} catch (InterfaceExistsException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S400_BAD_REQUEST, $e);
		} catch (WireguardInvalidEndpointException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S400_BAD_REQUEST, $e);
		} catch (WireguardKeyErrorException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	/**
	 * @Path("/{id}")
	 * @Method("PUT")
	 * @OpenApi("
	 *  summary: Edits an existing Wireguard VPN tunnel
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
	 *      '400':
	 *          $ref: '#/components/responses/BadRequest'
	 *      '500':
	 *          $ref: '#/components/responses/ServerError'
	 * ")
	 * @RequestParameters(
	 *     @RequestParameter(name="id", type="integer", description="Wireguard tunnel id")
	 * )
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function edit(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validator->validateRequest('wireguardTunnel', $request);
		try {
			$id = (int) $request->getParameter('id');
			$this->wireguardManager->editInterface($id, $request->getJsonBody(false));
			return $response->writeBody('Workaround');
		} catch (NonexistentWireguardTunnelException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S404_NOT_FOUND, $e);
		} catch (WireguardInvalidEndpointException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S400_BAD_REQUEST, $e);
		} catch (WireguardKeyErrorException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	/**
	 * @Path("/{id}")
	 * @Method("DELETE")
	 * @OpenApi("
	 *  summary: Removes an existing Wireguard VPN tunnel
	 *  responses:
	 *      '200':
	 *          description: Success
	 *      '404':
	 *          description: Not found
	 *      '500':
	 *          $ref: '#/components/responses/ServerError'
	 * ")
	 * @RequestParameters(
	 *     @RequestParameter(name="id", type="integer", description="Wireguard tunnel ID")
	 * )
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function remove(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			$id = (int) $request->getParameter('id');
			$this->wireguardManager->removeInterface($id);
			return $response->writeBody('Workaround');
		} catch (NonexistentWireguardTunnelException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S404_NOT_FOUND, $e);
		} catch (UnsupportedInitSystemException $e) {
			throw new ServerErrorException('Unsupported init system', ApiResponse::S500_INTERNAL_SERVER_ERROR);
		} catch (NonexistentServiceException $e) {
			throw new ClientErrorException('Wireguard tunnel not found', ApiResponse::S404_NOT_FOUND);
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
