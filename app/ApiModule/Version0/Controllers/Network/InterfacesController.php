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
use App\NetworkModule\Enums\InterfaceTypes;
use App\NetworkModule\Exceptions\NetworkManagerException;
use App\NetworkModule\Exceptions\NonexistentDeviceException;
use App\NetworkModule\Models\InterfaceManager;

/**
 * Network interfaces controller
 */
#[Path('/interfaces')]
#[Tag('IP network - Network interfaces')]
class InterfacesController extends BaseNetworkController {

	/**
	 * Constructor
	 * @param InterfaceManager $manager Network interface manager
	 * @param ControllerValidators $validators Controller validators
	 */
	public function __construct(
		private readonly InterfaceManager $manager,
		ControllerValidators $validators,
	) {
		parent::__construct($validators);
	}

	#[Path('/')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Returns network interfaces
		parameters:
			-
				in: query
				name: type
				schema:
					type: string
					enum:
						- 'bond'
						- 'bt'
						- 'bridge'
						- 'dummy'
						- 'ethernet'
						- 'gsm'
						- 'iptunnel'
						- 'loopback'
						- 'ppp'
						- 'tun'
						- 'vlan'
						- 'wifi'
						- 'wifi-p2p'
						- 'wireguard'
				required: false
				description: Connection type
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/NetworkInterfaces'
			'403':
				$ref: '#/components/responses/Forbidden'
			'500':
				$ref: '#/components/responses/ServerError'
	EOT)]
	public function list(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['network']);
		$typeParam = $request->getQueryParam('type', null);
		$type = $typeParam === null ? null : InterfaceTypes::tryFrom($typeParam);
		$list = $this->manager->list($type);
		$response = $response->writeJsonBody($list);
		return $this->validators->validateResponse('networkInterfaces', $response);
	}

	#[Path('/{name}/connect')]
	#[Method('POST')]
	#[OpenApi(<<<'EOT'
		summary: Connects network interface
		responses:
			'200':
				description: Success
			'400':
				$ref: '#/components/responses/BadRequest'
			'403':
				$ref: '#/components/responses/Forbidden'
			'500':
				$ref: '#/components/responses/ServerError'
	EOT)]
	#[RequestParameter(name: 'name', type: 'string', description: 'Network interface name')]
	public function connect(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['network']);
		try {
			$this->manager->connect($request->getParameter('name'));
			return $response;
		} catch (NonexistentDeviceException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S404_NOT_FOUND, $e);
		} catch (NetworkManagerException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	#[Path('/{name}/disconnect')]
	#[Method('POST')]
	#[OpenApi(<<<'EOT'
		summary: Disconnects network interface
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
	#[RequestParameter(name: 'name', type: 'string', description: 'Network interface name')]
	public function disconnect(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['network']);
		try {
			$this->manager->disconnect($request->getParameter('name'));
			return $response;
		} catch (NonexistentDeviceException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S404_NOT_FOUND, $e);
		} catch (NetworkManagerException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

}
