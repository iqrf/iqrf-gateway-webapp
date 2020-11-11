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

namespace App\ApiModule\Version0\Controllers\Gateway;

use Apitte\Core\Adjuster\FileResponseAdjuster;
use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\OpenApi;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Exception\Api\ServerErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Controllers\GatewayController;
use App\ApiModule\Version0\Models\RestApiSchemaValidator;
use App\GatewayModule\Exceptions\LogNotFoundException;
use App\GatewayModule\Models\LogManager;
use DateTime;
use Nette\Utils\FileSystem;
use Throwable;

/**
 * Log controller
 * @Path("/")
 */
class LogController extends GatewayController {

	/**
	 * @var LogManager Log manager
	 */
	private $logManager;

	/**
	 * Constructor
	 * @param LogManager $logManager Log manager
	 * @param RestApiSchemaValidator $validator REST API JSON schema validator
	 */
	public function __construct(LogManager $logManager, RestApiSchemaValidator $validator) {
		$this->logManager = $logManager;
		parent::__construct($validator);
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

}
