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
use Apitte\Core\Annotation\Controller\RequestBody;
use Apitte\Core\Annotation\Controller\RequestParameter;
use Apitte\Core\Annotation\Controller\RequestParameters;
use Apitte\Core\Annotation\Controller\Response;
use Apitte\Core\Annotation\Controller\Responses;
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\NetworkModule\Exceptions\NetworkManagerException;
use App\NetworkModule\Models\ConnectionManager;
use App\NetworkModule\Models\ConnectivityManager;
use App\NetworkModule\Models\InterfaceManager;
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
	 * Constructor
	 * @param ConnectionManager $connectionManager Network connection manager
	 * @param ConnectivityManager $connectivityManager Network connectivity manager
	 * @param InterfaceManager $interfaceManager Network interface manager
	 */
	public function __construct(ConnectionManager $connectionManager, ConnectivityManager $connectivityManager, InterfaceManager $interfaceManager) {
		$this->connectionManger = $connectionManager;
		$this->connectivityManager = $connectivityManager;
		$this->interfaceManager = $interfaceManager;
	}

	/**
	 * @Path("/connections")
	 * @Method("GET")
	 * @OpenApi("
	 *   summary: Returns network connections
	 * ")
	 * @Responses({
	 *      @Response(code="200", description="Success", entity="\App\ApiModule\Version0\Entities\Response\NetworkConnectionEntity[]")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function listConnections(ApiRequest $request, ApiResponse $response): ApiResponse {
		$list = $this->connectionManger->list();
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
	 *   summary: Edits network connection by its UUID
	 * ")
	 * @RequestBody(entity="\App\ApiModule\Version0\Entities\Response\NetworkConnectionDetailEntity", description="Network connection")
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
			$connection = $this->connectionManger->get($uuid);
			$this->connectionManger->edit($connection, $request->getJsonBody(false));
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
	 *   summary: Returns network connection by its UUID
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="uuid", type="string", description="Connection UUID")
	 * })
	 * @Responses({
	 *     @Response(code="200", description="Success", entity="\App\ApiModule\Version0\Entities\Response\NetworkConnectionDetailEntity"),
	 *     @Response(code="400", description="Bad request")
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
	 *   summary: Checks network connectivity
	 * ")
	 * @Responses({
	 *     @Response(code="200", description="Success"),
	 *     @Response(code="400", description="Bad request")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function checkConnectivity(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			$state = $this->connectivityManager->check()->toScalar();
			return $response->writeJsonBody(['state' => $state]);
		} catch (NetworkManagerException $e) {
			return $response->withStatus(400)
				->writeJsonBody(['error' => $e->getMessage()]);
		}
	}

	/**
	 * @Path("/interfaces")
	 * @Method("GET")
	 * @OpenApi("
	 *   summary: Returns network interfaces
	 * ")
	 * @Responses({
	 *      @Response(code="200", description="Success", entity="\App\ApiModule\Version0\Entities\Response\NetworkInterfaceEntity[]")
	 * })
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

}
