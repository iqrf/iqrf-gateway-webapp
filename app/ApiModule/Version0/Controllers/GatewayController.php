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
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Annotation\Controller\Response;
use Apitte\Core\Annotation\Controller\Responses;
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\GatewayModule\Models\InfoManager;
use App\GatewayModule\Models\LogManager;
use App\GatewayModule\Models\PowerManager;
use DateTime;
use Nette\Utils\FileSystem;

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
	 * Returns information about the gateway
	 * @Path("/info")
	 * @Method("GET")
	 * @Responses({
	 *      @Response(code="200", description="Success")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function info(ApiRequest $request, ApiResponse $response): ApiResponse {
		$info = $this->infoManager->get();
		return $response->writeJsonBody($info);
	}

	/**
	 * Returns latest IQRF Gateway Daemon's log
	 * @Path("/log")
	 * @Method("GET")
	 * @Responses({
	 *      @Response(code="200", description="Success")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function log(ApiRequest $request, ApiResponse $response): ApiResponse {
		$headers = [
			'Content-Type' => 'text/plain; charset=utf-8',
		];
		return $response->withHeaders($headers)
			->writeBody($this->logManager->load());
	}

	/**
	 * Returns archive with IQRF Gateway Daemon's logs
	 * @Path("/logs")
	 * @Method("GET")
	 * @Responses({
	 *      @Response(code="200", description="Success")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function logArchive(ApiRequest $request, ApiResponse $response): ApiResponse {
		$path = $this->logManager->getArchive();
		$now = new DateTime();
		$fileName = 'iqrf-gateway-daemon-logs' . $now->format('c') . '.zip';
		$headers = [
			'Content-Type' => 'application/zip',
			'Content-Disposition' => 'attachment; filename="' . $fileName . '"; filename*=utf-8\'\'' . rawurlencode($fileName),
			'Content-Length' => (string) filesize($path),
		];
		return $response->withHeaders($headers)
			->writeBody(FileSystem::read($path));
	}

	/**
	 * Powers off the gateway
	 * @Path("/poweroff")
	 * @Method("POST")
	 * @Responses({
	 *      @Response(code="200", description="Success")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function powerOff(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->powerManager->powerOff();
		return $response;
	}

	/**
	 * Reboots the gateway
	 * @Path("/reboot")
	 * @Method("POST")
	 * @Responses({
	 *      @Response(code="200", description="Success")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function reboot(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->powerManager->reboot();
		return $response;
	}

}
