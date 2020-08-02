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
use App\NetworkModule\Enums\ConnectionTypes;
use App\NetworkModule\Exceptions\NetworkManagerException;
use App\NetworkModule\Models\ConnectionManager;
use App\NetworkModule\Models\ConnectivityManager;
use App\NetworkModule\Models\InterfaceManager;
use App\NetworkModule\Models\WifiManager;
use Grifart\Enum\MissingValueDeclarationException;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Ramsey\Uuid\Uuid;

/**
 * Network manager
 * @Path("/network")
 * @Tag("Network network")
 */
class NetworkController extends BaseController {

	/**
	 * @var ConnectionManager Network connection manager
	 */
	private $connectionManger;

	/**
	 * @var ConnectivityManager Network connectivity manager
	 */
	private $connectivityManager;

	/**
	 * @var InterfaceManager Network interface manager
	 */
	private $interfaceManager;

	/**
	 * @var WifiManager WiFi network manager
	 */
	private $wifiManager;

	/**
	 * Constructor
	 * @param ConnectionManager $connectionManager Network connection manager
	 * @param ConnectivityManager $connectivityManager Network connectivity manager
	 * @param InterfaceManager $interfaceManager Network interface manager
	 * @param WifiManager $wifiManager WiFi network manager
	 */
	public function __construct(ConnectionManager $connectionManager, ConnectivityManager $connectivityManager, InterfaceManager $interfaceManager, WifiManager $wifiManager) {
		$this->connectionManger = $connectionManager;
		$this->connectivityManager = $connectivityManager;
		$this->interfaceManager = $interfaceManager;
		$this->wifiManager = $wifiManager;
	}

	/**
	 * @Path("/connections")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: Returns network connections
	 *  parameters:
	 *      - in: query
	 *        name: type
	 *        schema:
	 *          type: string
	 *          enum:
	 *              - 'bluetooth'
	 *              - 'bridge'
	 *              - 'dummy'
	 *              - '802-3-ethernet'
	 *              - 'gsm'
	 *              - 'infiniband'
	 *              - 'tun'
	 *              - 'vlan'
	 *              - 'vpn'
	 *              - '802-11-wireless'
	 *              - 'wimax'
	 *              - 'wireguard'
	 *              - 'wpan'
	 *        required: false
	 *        description: Connection type
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/NetworkConnections'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function listConnections(ApiRequest $request, ApiResponse $response): ApiResponse {
		$typeParam = $request->getQueryParam('type', null);
		try {
			$type = $typeParam === null ? null : ConnectionTypes::fromScalar($typeParam);
		} catch (MissingValueDeclarationException $e) {
			$type = null;
		}
		$list = $this->connectionManger->list($type);
		return $response->writeJsonBody($list);
	}

	/**
	 * @Path("/connections/{uuid}")
	 * @Method("DELETE")
	 * @OpenApi("
	 *   summary: Deletes network connection by its UUID
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="uuid", type="string", description="Connection UUID")
	 * })
	 * @Responses({
	 *     @Response(code="200", description="Success"),
	 *     @Response(code="400", description="Bad request")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function deleteConnection(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			$uuid = Uuid::fromString($request->getParameter('uuid'));
			$this->connectionManger->delete($uuid);
		} catch (InvalidUuidStringException $e) {
			return $response->withStatus(400)
				->writeJsonBody(['error' => 'Invalid UUID']);
		} catch (NetworkManagerException $e) {
			return $response->withStatus(400)
				->writeJsonBody(['error' => $e->getMessage()]);
		}
		return $response;
	}

	/**
	 * @Path("/connections/{uuid}")
	 * @Method("PUT")
	 * @OpenApi("
	 *  summary: Edits network connection by its UUID
	 *  requestBody:
	 *      description: Network connection configuration
	 *      required: true
	 *      content:
	 *          application/json:
	 *              schema:
	 *                  $ref: '#/components/schemas/NetworkConnection'
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="uuid", type="string", description="Connection UUID")
	 * })
	 * @Responses({
	 *     @Response(code="200", description="Success"),
	 *     @Response(code="400", description="Bad request")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function editConnection(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			$uuid = Uuid::fromString($request->getParameter('uuid'));
			$json = $request->getJsonBody(false);
			$this->connectionManger->edit($uuid, $json);
		} catch (InvalidUuidStringException $e) {
			return $response->withStatus(400)
				->writeJsonBody(['error' => 'Invalid UUID']);
		} catch (NetworkManagerException $e) {
			return $response->withStatus(400)
				->writeJsonBody(['error' => $e->getMessage()]);
		}
		return $response;
	}

	/**
	 * @Path("/connections/{uuid}")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: Returns network connection by its UUID
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/NetworkConnection'
	 *      '400':
	 *          description: Bad request
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="uuid", type="string", description="Connection UUID")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function getConnection(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			$uuid = Uuid::fromString($request->getParameter('uuid'));
			return $response->writeJsonObject($this->connectionManger->get($uuid));
		} catch (InvalidUuidStringException $e) {
			return $response->withStatus(400)
				->writeJsonBody(['error' => 'Invalid UUID']);
		} catch (NetworkManagerException $e) {
			return $response->withStatus(400)
				->writeJsonBody(['error' => $e->getMessage()]);
		}
	}

	/**
	 * @Path("/connectivity")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: Checks network connectivity
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/NetworkConnectivityState'
	 *      '500':
	 *          description: Server error
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function checkConnectivity(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			$state = $this->connectivityManager->check()->toScalar();
			return $response->writeJsonBody(['state' => $state]);
		} catch (NetworkManagerException $e) {
			return $response->withStatus(500)
				->writeJsonBody(['error' => $e->getMessage()]);
		}
	}

	/**
	 * @Path("/interfaces")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: Returns network interfaces
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/NetworkInterfaces'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function listInterfaces(ApiRequest $request, ApiResponse $response): ApiResponse {
		$list = $this->interfaceManager->list();
		return $response->writeJsonBody($list);
	}

	/**
	 * @Path("/interfaces/{name}/connect")
	 * @Method("POST")
	 * @OpenApi("
	 *   summary: Connects network interface
	 * ")
	 * @RequestParameters(
	 *     @RequestParameter(name="name", type="string", description="Network interface name")
	 * )
	 * @Responses({
	 *      @Response(code="200", description="Success")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function connectInterface(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->interfaceManager->connect($request->getParameter('name'));
		return $response;
	}

	/**
	 * @Path("/interfaces/{name}/disconnect")
	 * @Method("POST")
	 * @OpenApi("
	 *   summary: Disconnects network interface
	 * ")
	 * @RequestParameters(
	 *     @RequestParameter(name="name", type="string", description="Network interface name")
	 * )
	 * @Responses({
	 *      @Response(code="200", description="Success")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function disconnectInterface(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->interfaceManager->disconnect($request->getParameter('name'));
		return $response;
	}

	/**
	 * @Path("/wifi/list")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: Lists available WiFi access points
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/NetworkWifiList'
	 *      '500':
	 *          description: Server error
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function listWifi(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			return $response->writeJsonBody($this->wifiManager->list());
		} catch (NetworkManagerException $e) {
			return $response->withStatus(500)
				->writeJsonBody(['error' => $e->getMessage()]);
		}
	}

}
