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

namespace App\ApiModule\Version0\Controllers\Gateway;

use Apitte\Core\Adjuster\FileResponseAdjuster;
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
use App\GatewayModule\Exceptions\LogEmptyException;
use App\GatewayModule\Exceptions\LogNotFoundException;
use App\GatewayModule\Exceptions\ServiceLogNotAvailableException;
use App\GatewayModule\Models\LogManager;
use DateTime;
use Nette\Utils\FileSystem;
use Throwable;

/**
 * Log controller
 */
#[Path('/logs')]
#[Tag('Gateway - Logs')]
class LogController extends BaseGatewayController {

	/**
	 * Constructor
	 * @param LogManager $logManager Log manager
	 * @param ControllerValidators $validators Controller validators
	 */
	public function __construct(
		private readonly LogManager $logManager,
		ControllerValidators $validators,
	) {
		parent::__construct($validators);
	}

	#[Path('/')]
	#[Method('GET')]
	#[OpenApi('
		summary: Returns list of services with available logs
		responses:
			\'200\':
				description: \'Success\'
				content:
					application/json:
						schema:
							$ref: \'#/components/schemas/LogServices\'
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
	')]
	public function logServices(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['gateway:log']);
		$response = $response->writeJsonBody($this->logManager->getAvailableServices());
		return $this->validators->validateResponse('logServices', $response);
	}

	#[Path('/{service}')]
	#[Method('GET')]
	#[OpenApi('
		summary: Returns latest log of a service
		responses:
			\'200\':
				description: \'Success\'
				content:
					text/plain:
						schema:
							type: string
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
			\'404\':
				description: \'Service not found or log not found\'
				content:
					application/json:
						schema:
							$ref: \'#/components/schemas/Error\'
			\'500\':
				$ref: \'#/components/responses/ServerError\'
	')]
	#[RequestParameter(name: 'service', type: 'string', description: 'Service name')]
	public function log(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['gateway:log']);
		$service = $request->getParameter('service');
		try {
			return $response->withHeader('Content-Type', 'text/plain')
				->writeBody($this->logManager->getServiceLog($service));
		} catch (ServiceLogNotAvailableException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S404_NOT_FOUND, $e);
		} catch (LogNotFoundException $e) {
			throw new ClientErrorException('Log file does not exist.', ApiResponse::S404_NOT_FOUND, $e);
		} catch (LogEmptyException $e) {
			throw new ServerErrorException('Log file is empty.', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	#[Path('/export')]
	#[Method('GET')]
	#[OpenApi('
		summary: Returns archive with IQRF Gateway logs
		responses:
			\'200\':
				description: \'Success\'
				content:
					application/zip:
						schema:
							type: string
							format: binary
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
	')]
	public function logArchive(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['gateway:log']);
		$path = $this->logManager->createArchive();
		try {
			$now = new DateTime();
			$fileName = 'iqrf-gateway-logs_' . $now->format('c') . '.zip';
		} catch (Throwable) {
			$fileName = 'iqrf-gateway-logs.zip';
		}
		$response->writeBody(FileSystem::read($path));
		return FileResponseAdjuster::adjust($response, $response->getBody(), $fileName, 'application/zip');
	}

}
