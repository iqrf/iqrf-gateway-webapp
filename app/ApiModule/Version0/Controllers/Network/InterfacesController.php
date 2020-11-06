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
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Controllers\NetworkController;
use App\ApiModule\Version0\Models\RestApiSchemaValidator;
use App\NetworkModule\Enums\InterfaceTypes;
use App\NetworkModule\Models\InterfaceManager;
use Grifart\Enum\MissingValueDeclarationException;

/**
 * Network interfaces controller
 * @Path("/interfaces")
 */
class InterfacesController extends NetworkController {

	/**
	 * @var InterfaceManager Network interface manager
	 */
	private $interfaceManager;

	/**
	 * Constructor
	 * @param InterfaceManager $manager Network interface manager
	 * @param RestApiSchemaValidator $validator REST API JSON schema validator
	 */
	public function __construct(InterfaceManager $manager, RestApiSchemaValidator $validator) {
		$this->interfaceManager = $manager;
		parent::__construct($validator);
	}

	/**
	 * @Path("/")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: Returns network interfaces
	 *  parameters:
	 *      - in: query
	 *        name: type
	 *        schema:
	 *          type: string
	 *          enum:
	 *              - 'bond'
	 *              - 'bridge'
	 *              - 'dummy'
	 *              - 'ethernet'
	 *              - 'loopback'
	 *              - 'tun'
	 *              - 'vlan'
	 *              - 'wifi'
	 *              - 'wifi-p2p'
	 *        required: false
	 *        description: Connection type
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
	public function list(ApiRequest $request, ApiResponse $response): ApiResponse {
		$typeParam = $request->getQueryParam('type', null);
		try {
			$type = $typeParam === null ? null : InterfaceTypes::fromScalar($typeParam);
		} catch (MissingValueDeclarationException $e) {
			$type = null;
		}
		$list = $this->interfaceManager->list($type);
		return $response->writeJsonBody($list);
	}

	/**
	 * @Path("/{name}/connect")
	 * @Method("POST")
	 * @OpenApi("
	 *  summary: Connects network interface
	 *  responses:
	 *      '200':
	 *          description: Success
	 * ")
	 * @RequestParameters(
	 *     @RequestParameter(name="name", type="string", description="Network interface name")
	 * )
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function connect(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->interfaceManager->connect($request->getParameter('name'));
		return $response->writeBody('Workaround');
	}

	/**
	 * @Path("/{name}/disconnect")
	 * @Method("POST")
	 * @OpenApi("
	 *  summary: Disconnects network interface
	 *  responses:
	 *      '200':
	 *          description: Success
	 * ")
	 * @RequestParameters(
	 *     @RequestParameter(name="name", type="string", description="Network interface name")
	 * )
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function disconnect(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->interfaceManager->disconnect($request->getParameter('name'));
		return $response->writeBody('Workaround');
	}

}
