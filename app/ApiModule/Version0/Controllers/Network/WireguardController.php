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
use App\NetworkModule\Exceptions\IpKernelException;
use App\NetworkModule\Exceptions\IpSyntaxException;
use App\NetworkModule\Exceptions\NonexistentDeviceException;
use App\NetworkModule\Exceptions\WireguardKeyExistsException;
use App\NetworkModule\Models\WireguardManager;

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
	 * @Path("/interface/{name}")
	 * @Method("POST")
	 * @OpenApi("
	 *  summary: Creates a new Wireguard VPN interface
	 *  responses:
	 *      '200':
	 *          description: Success
	 *      '400':
	 *          $ref: '#/components/responses/BadRequest'
	 *      '500':
	 *          $ref: '#/components/responses/ServerError'
	 * ")
	 * @RequestParameters(
	 *     @RequestParameter(name="name", type="string", description="Wireguard VPN interface name")
	 * )
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function createInterface(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			$this->wireguardManager->createInterface($request->getParameter('name'));
			return $response->writeBody('Workaround');
		} catch (IpSyntaxException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S400_BAD_REQUEST);
		} catch (IpKernelException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR);
		}
	}

	/**
	 * @Path("/interface/{name}")
	 * @Method("DELETE")
	 * @OpenApi("
	 *  summary: Removes a Wireguard VPN interface
	 *  responses:
	 *      '200':
	 *          description: Success
	 *      '400':
	 *          $ref: '#/components/responses/BadRequest'
	 *      '500':
	 *          $ref: '#/components/responses/ServerError'
	 * ")
	 * @RequestParameters(
	 *     @RequestParameter(name="name", type="string", description="Wireguard VPN interface name")
	 * )
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function removeInterface(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			$this->wireguardManager->removeInterface($request->getParameter('name'));
			return $response->writeBody('Workaround');
		} catch (IpSyntaxException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S400_BAD_REQUEST);
		} catch (NonexistentDeviceException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S404_NOT_FOUND);
		} catch (IpKernelException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR);
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
	 *      '409':
	 *          description: Already exists
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function generateKeys(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			$result = $this->wireguardManager->generateKeys();
			return $response->writeJsonBody($result);
		} catch (WireguardKeyExistsException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR);
		}
	}

}
