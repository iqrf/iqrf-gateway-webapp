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
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Exception\Api\ClientErrorException;
use Apitte\Core\Exception\Api\ServerErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Models\ControllerValidators;
use App\NetworkModule\Enums\ConnectionTypes;
use App\NetworkModule\Exceptions\NetworkManagerException;
use App\NetworkModule\Exceptions\NonexistentConnectionException;
use App\NetworkModule\Models\ConnectionManager;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * Network connections controller
 */
#[Path('/connections')]
#[Tag('IP network - Network connections')]
class ConnectionsController extends BaseNetworkController {

	/**
	 * Constructor
	 * @param ConnectionManager $manager Network connection manager
	 * @param ControllerValidators $validators Controller validators
	 */
	public function __construct(
		private readonly ConnectionManager $manager,
		ControllerValidators $validators,
	) {
		parent::__construct($validators);
	}

	#[Path('/')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Returns network connections
		parameters:
			-
				in: query
				name: type
				schema:
					type: string
					enum:
						- 'bluetooth'
						- 'bridge'
						- 'dummy'
						- '802-3-ethernet'
						- 'gsm'
						- 'infiniband'
						- 'ip-tunnel'
						- 'loopback'
						- 'tun'
						- 'vlan'
						- 'vpn'
						- '802-11-wireless'
						- 'wimax'
						- 'wireguard'
						- 'wpan'
				required: false
				description: Connection type
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/NetworkConnections'
			'403':
				$ref: '#/components/responses/Forbidden'
	EOT)]
	public function list(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['network']);
		$typeParam = $request->getQueryParam('type', null);
		$type = $typeParam === null ? null : ConnectionTypes::tryFrom($typeParam);
		$list = $this->manager->list($type);
		$response = $response->writeJsonBody($list);
		return $this->validators->validateResponse('networkConnections', $response);
	}

	#[Path('/{uuid}')]
	#[Method('DELETE')]
	#[OpenApi(<<<'EOT'
		summary: Deletes network connection by its UUID
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
				$ref: '#/components/responses/ServerError'
	EOT)]
	#[RequestParameter(name: 'uuid', type: 'string', description: 'Connection UUID')]
	public function delete(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['network']);
		try {
			$uuid = $this->getUuid($request);
			$this->manager->delete($uuid);
		} catch (NonexistentConnectionException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S404_NOT_FOUND, $e);
		} catch (NetworkManagerException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
		return $response;
	}

	#[Path('/')]
	#[Method('POST')]
	#[OpenApi(<<<'EOT'
		summary: Creates new network connection
		requestBody:
			description: Network connection configuration
			required: true
			content:
				application/json:
					schema:
						$ref: '#/components/schemas/NetworkConnection'
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/NetworkConnectionCreated'
			'400':
				$ref: '#/components/responses/BadRequest'
			'403':
				$ref: '#/components/responses/Forbidden'
			'500':
				$ref: '#/components/responses/ServerError'
	EOT)]
	public function add(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['network']);
		$this->validators->validateRequest('networkConnection', $request);
		try {
			$json = $request->getJsonBodyCopy(false);
			$uuid = $this->manager->add($json);
			$response = $response->writeJsonBody(['uuid' => $uuid]);
			return $this->validators->validateResponse('networkConnectionCreated', $response);
		} catch (NetworkManagerException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	#[Path('/{uuid}')]
	#[Method('PUT')]
	#[OpenApi(<<<'EOT'
		summary: Edits network connection by its UUID
		requestBody:
			description: Network connection configuration
			required: true
			content:
				application/json:
					schema:
						$ref: '#/components/schemas/NetworkConnection'
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
				$ref: '#/components/responses/ServerError'
	EOT)]
	#[RequestParameter(name: 'uuid', type: 'string', description: 'Connection UUID')]
	public function edit(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['network']);
		try {
			$uuid = $this->getUuid($request);
			$this->validators->validateRequest('networkConnection', $request);
			$json = $request->getJsonBodyCopy(false);
			$this->manager->edit($uuid, $json);
		} catch (NonexistentConnectionException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S404_NOT_FOUND, $e);
		} catch (NetworkManagerException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
		return $response;
	}

	#[Path('/{uuid}')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Returns network connection by its UUID
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/NetworkConnection'
			'400':
				$ref: '#/components/responses/BadRequest'
			'403':
				$ref: '#/components/responses/Forbidden'
			'404':
				$ref: '#/components/responses/NotFound'
			'500':
				$ref: '#/components/responses/ServerError'
	EOT)]
	#[RequestParameter(name: 'uuid', type: 'string', description: 'Connection UUID')]
	public function get(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['network']);
		try {
			$uuid = $this->getUuid($request);
			$response = $response->writeJsonBody($this->manager->get($uuid)->jsonSerialize());
			return $this->validators->validateResponse('networkConnection', $response);
		} catch (NonexistentConnectionException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S404_NOT_FOUND, $e);
		} catch (NetworkManagerException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	#[Path('/{uuid}/connect')]
	#[Method('POST')]
	#[OpenApi(<<<'EOT'
		summary: Connects network connection
		parameters:
			-
				in: query
				name: interface
				schema:
					type: string
				required: false
				description: Network interface name
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
				$ref: '#/components/responses/ServerError'
	EOT)]
	#[RequestParameter(name: 'uuid', type: 'string', description: 'Connection UUID')]
	public function connect(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['network']);
		$interface = $request->getQueryParam('interface', null);
		try {
			$uuid = $this->getUuid($request);
			$this->manager->up($uuid, $interface);
			return $response;
		} catch (NonexistentConnectionException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S404_NOT_FOUND, $e);
		} catch (NetworkManagerException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	#[Path('/{uuid}/disconnect')]
	#[Method('POST')]
	#[OpenApi(<<<'EOT'
		summary: Disconnects network connection
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
				$ref: '#/components/responses/ServerError'
	EOT)]
	#[RequestParameter(name: 'uuid', type: 'string', description: 'Connection UUID')]
	public function disconnect(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['network']);
		try {
			$uuid = $this->getUuid($request);
			$this->manager->down($uuid);
			return $response;
		} catch (NonexistentConnectionException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S404_NOT_FOUND, $e);
		} catch (NetworkManagerException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	/**
	 * Returns the network connection UUID
	 * @param ApiRequest $request API request
	 * @return UuidInterface Network connection UUID
	 * @throws ClientErrorException Invalid UUID
	 */
	private function getUuid(ApiRequest $request): UuidInterface {
		try {
			return Uuid::fromString($request->getParameter('uuid'));
		} catch (InvalidUuidStringException $e) {
			throw new ClientErrorException('Invalid UUID', ApiResponse::S400_BAD_REQUEST, $e);
		}
	}

}
