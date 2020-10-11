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

use Apitte\Core\Adjuster\FileResponseAdjuster;
use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\OpenApi;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Exception\Api\ServerErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\GatewayModule\Exceptions\LogNotFoundException;
use App\GatewayModule\Models\InfoManager;
use App\GatewayModule\Models\LogManager;
use App\GatewayModule\Models\PowerManager;
use DateTime;
use Nette\Utils\FileSystem;
use Throwable;

/**
 * Gateway manager controller
 * @Path("/gateway")
 * @Tag("Gateway manager")
 */
class GatewayController extends BaseController {

	/**
	 * @var InfoManager Gateway info manager
	 */
	private $infoManager;

	/**
	 * @var LogManager Log manager
	 */
	private $logManager;

	/**
	 * @var PowerManager Gateway power manager
	 */
	private $powerManager;

	/**
	 * Constructor
	 * @param InfoManager $infoManager Gateway info manager
	 * @param LogManager $logManager Log manager
	 * @param PowerManager $powerManager Gateway power manager
	 */
	public function __construct(InfoManager $infoManager, LogManager $logManager, PowerManager $powerManager) {
		$this->infoManager = $infoManager;
		$this->logManager = $logManager;
		$this->powerManager = $powerManager;
	}

	/**
	 * @Path("/info")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: Returns information about the gateway
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/GatewayInfo'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function info(ApiRequest $request, ApiResponse $response): ApiResponse {
		$info = $this->infoManager->get();
		return $response->writeJsonBody($info);
	}

	/**
	 * @Path("/log")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: 'Returns latest IQRF Gateway Daemon log'
	 *  responses:
	 *      '200':
	 *          description: 'Success'
	 *          content:
	 *              text/plain:
	 *                  schema:
	 *                      type: string
	 *      '500':
	 *          $ref: '#/components/responses/ServerError'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function log(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			$response->writeBody($this->logManager->load());
			$fileName = 'iqrf-gateway-daemon.log';
			$contentType = 'text/plain; charset=utf-8';
			return FileResponseAdjuster::adjust($response, $response->getBody(), $fileName, $contentType);
		} catch (LogNotFoundException $e) {
			throw new ServerErrorException('Log file not found', ApiResponse::S500_INTERNAL_SERVER_ERROR);
		}
	}

	/**
	 * @Path("/logs")
	 * @Method("GET")
	 * @OpenApi("
	 *   summary: Returns archive with IQRF Gateway Daemon logs
	 *   responses:
	 *     '200':
	 *       description: 'Success'
	 *       content:
	 *         application/zip:
	 *           schema:
	 *             type: string
	 *             format: binary
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function logArchive(ApiRequest $request, ApiResponse $response): ApiResponse {
		$path = $this->logManager->createArchive();
		try {
			$now = new DateTime();
			$fileName = 'iqrf-gateway-daemon-logs_' . $now->format('c') . '.zip';
		} catch (Throwable $e) {
			$fileName = 'iqrf-gateway-daemon-logs.zip';
		}
		$response->writeBody(FileSystem::read($path));
		return FileResponseAdjuster::adjust($response, $response->getBody(), $fileName, 'application/zip');
	}

	/**
	 * @Path("/poweroff")
	 * @Method("POST")
	 * @OpenApi("
	 *  summary: Powers off the gateway
	 *  responses:
	 *      '200':
	 *          description: Success
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function powerOff(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->powerManager->powerOff();
		return $response->writeBody('Workaround');
	}

	/**
	 * @Path("/reboot")
	 * @Method("POST")
	 * @OpenApi("
	 *  summary: Reboots the gateway
	 *  responses:
	 *      '200':
	 *          description: Success
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function reboot(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->powerManager->reboot();
		return $response->writeBody('Workaround');
	}

}
