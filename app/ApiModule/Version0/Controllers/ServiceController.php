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
use Apitte\Core\Annotation\Controller\Response;
use Apitte\Core\Annotation\Controller\Responses;
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ServiceModule\Exceptions\NotSupportedInitSystemException;
use App\ServiceModule\Models\ServiceManager;

/**
 * IQRF Gateway Daemon service controller
 * @Path("/service")
 * @Tag("Service manager")
 */
class ServiceController extends BaseController {

	/**
	 * @var ServiceManager IQRF Gateway Daemon's service manager
	 */
	private $manager;

	/**
	 * Constructor
	 * @param ServiceManager $manager IQRF Gateway Daemon's service manager
	 */
	public function __construct(ServiceManager $manager) {
		$this->manager = $manager;
	}

	/**
	 * @Path("/")
	 * @Method("GET")
	 * @OpenApi("
	 *   summary: Returns IQRF Gateway Daemon's service status
	 *   responses:
	 *     '200':
	 *       description: 'Success'
	 *       content:
	 *         text/plain:
	 *           schema:
	 *             type: string
	 *     '400':
	 *       description: 'Unsupported init system'
	 * ")
	 * @Responses({
	 *      @Response(code="200", description="Success"),
	 *      @Response(code="400", description="Unsupported init system")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function get(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			return $response->writeBody($this->manager->getStatus())
				->withHeader('Content-Type', 'text/plain; charset=utf-8');
		} catch (NotSupportedInitSystemException $e) {
			return $response->withStatus(400, 'Unsupported init system');
		}
	}

	/**
	 * @Path("/start")
	 * @Method("POST")
	 * @OpenApi("
	 *   summary: Starts IQRF Gateway Daemon's service
	 * ")
	 * @Responses({
	 *      @Response(code="200", description="Success"),
	 *      @Response(code="400", description="Unsupported init system")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function start(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			$this->manager->start();
			return $response;
		} catch (NotSupportedInitSystemException $e) {
			return $response->withStatus(400, 'Unsupported init system');
		}
	}

	/**
	 * @Path("/stop")
	 * @Method("POST")
	 * @OpenApi("
	 *   summary: Stops IQRF Gateway Daemon's service
	 * ")
	 * @Responses({
	 *      @Response(code="200", description="Success"),
	 *      @Response(code="400", description="Unsupported init system")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function stop(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			$this->manager->stop();
			return $response;
		} catch (NotSupportedInitSystemException $e) {
			return $response->withStatus(400, 'Unsupported init system');
		}
	}

	/**
	 * @Path("/restart")
	 * @Method("POST")
	 * @OpenApi("
	 *   summary: Restarts IQRF Gateway Daemon's service
	 * ")
	 * @Responses({
	 *      @Response(code="200", description="Success"),
	 *      @Response(code="400", description="Unsupported init system")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function restart(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			$this->manager->restart();
			return $response;
		} catch (NotSupportedInitSystemException $e) {
			return $response->withStatus(400, 'Unsupported init system');
		}
	}

}
