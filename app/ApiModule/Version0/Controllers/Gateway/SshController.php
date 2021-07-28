<?php

/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
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

namespace App\ApiModule\Version0\Controllers\Gateway;

use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\OpenApi;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Annotation\Controller\RequestParameter;
use Apitte\Core\Annotation\Controller\RequestParameters;
use Apitte\Core\Exception\Api\ClientErrorException;
use Apitte\Core\Exception\Api\ServerErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Controllers\GatewayController;
use App\ApiModule\Version0\Models\RestApiSchemaValidator;
use App\GatewayModule\Exceptions\SshDirectoryException;
use App\GatewayModule\Exceptions\SshInvalidKeyException;
use App\GatewayModule\Exceptions\SshKeyNotFoundException;
use App\GatewayModule\Exceptions\SshUtilityException;
use App\GatewayModule\Models\SshManager;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Nette\IOException;

/**
 * Gateway SSH controller
 * @Path("/ssh")
 */
class SshController extends GatewayController {

	/**
	 * @var SshManager SSH manager
	 */
	private $manager;

	/**
	 * Constructor
	 * @param RestApiSchemaValidator $validator REST API JSON schema validator
	 * @param SshManager $sshManager SSH manager
	 */
	public function __construct(RestApiSchemaValidator $validator, SshManager $sshManager) {
		$this->manager = $sshManager;
		parent::__construct($validator);
	}

	/**
	 * @Path("/keyTypes")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: Lists SSH key types
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/sshKeyTypes'
	 *      '500':
	 *          $ref: '#/components/responses/ServerError'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function listKeyTypes(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			return $response->writeJsonBody($this->manager->listKeyTypes());
		} catch (SshUtilityException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	/**
	 * @Path("/keys")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: List authorized SSH public keys
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schema/sshKeyList'
	 *      '500':
	 *          $ref: '#/components/responses/ServerError'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function listKeys(ApiRequest $request, ApiResponse $response): ApiResponse {
		$keys = $this->manager->listKeys();
		return $response->writeJsonBody($keys);
	}

	/**
	 * @Path("/key/{id}")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: Retrieves authorized SSH public key
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              text/plain:
	 *                  schema:
	 *                      type: string
	 *      '404':
	 *          description: Not found
	 *      '500':
	 *          $ref: '#/components/responses/ServerError'
	 * ")
	 * @RequestParameters({
	 *     @RequestParameter(name="id", type="integer", description="SSH public key ID")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function getKey(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			$id = (int) $request->getParameter('id');
			$key = $this->manager->getKey($id);
			return $response->writeBody($key->toString());
		} catch (SshKeyNotFoundException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S404_NOT_FOUND, $e);
		}
	}

	/**
	 * @Path("/keys")
	 * @Method("POST")
	 * @OpenApi("
	 *  summary: Adds SSH keys for key-based authentication
	 *  requestBody:
	 *      required: true
	 *      content:
	 *          application/json:
	 *              schema:
	 *                  $ref: '#/components/schemas/sshKeys'
	 *  responses:
	 *      '201':
	 *          description: Success
	 *      '400':
	 *          $ref: '#/components/responses/BadRequest'
	 *      '409':
	 *          description: SSH public key already exists
	 *      '500':
	 *          $ref: '#/components/responses/ServerError'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function addKeys(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validator->validateRequest('sshKeysAdd', $request);
		try {
			$this->manager->addKeys($request->getJsonBody(true));
			return $response->withStatus(ApiResponse::S201_CREATED)->writeBody('Workaround');
		} catch (SshInvalidKeyException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S400_BAD_REQUEST, $e);
		} catch (UniqueConstraintViolationException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S409_CONFLICT, $e);
		} catch (IOException | SshDirectoryException | SshUtilityException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	/**
	 * @Path("/key/{id}")
	 * @Method("DELETE")
	 * @OpenApi("
	 *  summary: Removes an authorized SSH public key
	 *  responses:
	 *      '200':
	 *          description: Success
	 *      '404':
	 *          description: Not found
	 * ")
	 * @RequestParameters({
	 *     @RequestParameter(name="id", type="integer", description="SSH public key ID")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function deleteKey(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			$id = (int) $request->getParameter('id');
			$this->manager->deleteKey($id);
			return $response->writeBody('Workaround');
		} catch (SshKeyNotFoundException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S404_NOT_FOUND, $e);
		}
	}

}
