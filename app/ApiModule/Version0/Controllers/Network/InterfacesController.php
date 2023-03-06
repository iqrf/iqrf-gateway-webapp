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
use App\NetworkModule\Enums\InterfaceTypes;
use App\NetworkModule\Exceptions\NetworkManagerException;
use App\NetworkModule\Exceptions\NonexistentDeviceException;
use App\NetworkModule\Models\InterfaceManager;
use Grifart\Enum\MissingValueDeclarationException;

/**
 * Network interfaces controller
 * @Path("/interfaces")
 */
class InterfacesController extends NetworkController {

	/**
	 * Constructor
	 * @param InterfaceManager $manager Network interface manager
	 * @param RestApiSchemaValidator $validator REST API JSON schema validator
	 */
	public function __construct(
		private readonly InterfaceManager $manager,
		RestApiSchemaValidator $validator,
	) {
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
	 *      '403':
	 *          $ref: '#/components/responses/Forbidden'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function list(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['network']);
		$typeParam = $request->getQueryParam('type', null);
		try {
			$type = $typeParam === null ? null : InterfaceTypes::fromScalar($typeParam);
		} catch (MissingValueDeclarationException $e) {
			$type = null;
		}
		$list = $this->manager->list($type);
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
	 *      '400':
	 *          $ref: '#/components/responses/BadRequest'
	 *      '403':
	 *          $ref: '#/components/responses/Forbidden'
	 *      '500':
	 *          $ref: '#/components/responses/ServerError'
	 * ")
	 * @RequestParameters(
	 *     @RequestParameter(name="name", type="string", description="Network interface name")
	 * )
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function connect(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['network']);
		try {
			$this->manager->connect($request->getParameter('name'));
			return $response->writeBody('Workaround');
		} catch (NonexistentDeviceException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S404_NOT_FOUND, $e);
		} catch (NetworkManagerException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	/**
	 * @Path("/{name}/disconnect")
	 * @Method("POST")
	 * @OpenApi("
	 *  summary: Disconnects network interface
	 *  responses:
	 *      '200':
	 *          description: Success
	 *      '400':
	 *          $ref: '#/components/responses/BadRequest'
	 *      '403':
	 *          $ref: '#/components/responses/Forbidden'
	 *      '500':
	 *          $ref: '#/components/responses/ServerError'
	 * ")
	 * @RequestParameters(
	 *     @RequestParameter(name="name", type="string", description="Network interface name")
	 * )
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function disconnect(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['network']);
		try {
			$this->manager->disconnect($request->getParameter('name'));
			return $response->writeBody('Workaround');
		} catch (NonexistentDeviceException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S404_NOT_FOUND, $e);
		} catch (NetworkManagerException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

}
