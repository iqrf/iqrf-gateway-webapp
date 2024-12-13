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

use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\OpenApi;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Exception\Api\ClientErrorException;
use Apitte\Core\Exception\Api\ServerErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Controllers\Config\JournalController as JournalConfigController;
use App\ApiModule\Version0\Models\ControllerValidators;
use App\GatewayModule\Exceptions\JournalReaderArgumentException;
use App\GatewayModule\Exceptions\JournalReaderInternalException;
use App\GatewayModule\Models\JournalReaderManager;

/**
 * Journal controller
 */
#[Path('/journal')]
class JournalController extends BaseGatewayController {

	/**
	 * Constructor
	 * @param JournalConfigController $configController New Journal config controller
	 * @param JournalReaderManager $readerManager Journal reader manager
	 * @param ControllerValidators $validators Controller validators
	 */
	public function __construct(
		private readonly JournalConfigController $configController,
		private readonly JournalReaderManager $readerManager,
		ControllerValidators $validators,
	) {
		parent::__construct($validators);
	}

	#[Path('/config')]
	#[Tag('Configuration - Logs')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Returns journal configuration
		deprecated: true
		description: "Deprecated in favor of the new Journal config controller, use `GET` `/config/journal` instead. Will be removed in the version 3.1.0."
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/Journal'
			'403':
				$ref: '#/components/responses/Forbidden'
			'500':
				$ref: '#/components/responses/ServerError'
	EOT)]
	public function getConfig(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $this->configController->getConfig($request, $response);
	}

	#[Path('/config')]
	#[Tag('Configuration - Logs')]
	#[Method('PUT')]
	#[OpenApi(<<<'EOT'
		summary: Updates journal configuration
		deprecated: true
		description: "Deprecated in favor of the new Journal config controller, use `PUT` `/config/journal` instead. Will be removed in the version 3.1.0."
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: '#/components/schemas/Journal'
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
	public function saveConfig(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $this->configController->saveConfig($request, $response);
	}

	#[Path('/config')]
	#[Tag('Configuration - Logs')]
	#[Method('POST')]
	#[OpenApi(<<<'EOT'
		summary: Updates journal configuration
		deprecated: true
		description: "Deprecated in favor of the new Journal config controller, use `PUT` `/config/journal` instead. Will be removed in the version 3.1.0."
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: '#/components/schemas/Journal'
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
	public function saveConfigOld(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $this->configController->saveConfig($request, $response);
	}

	#[Path('/')]
	#[Method('GET')]
	#[Tag('Gateway - Logs')]
	#[OpenApi(<<<'EOT'
		summary: Returns journal records
		parameters:
			-
				in: query
				name: count
				schema:
					type: integer
					minimum: 1
					maximum: 1000
					default: 500
				required: false
				description: Number of last records to retrieve
			-
				in: query
				name: cursor
				schema:
					type: string
				required: false
				description: Specifies a record cursor to start from
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/JournalRecords'
			'403':
				$ref: '#/components/responses/Forbidden'
			'500':
				$ref: '#/components/responses/ServerError'
	EOT)]
	public function get(ApiRequest $request, ApiResponse $response): ApiResponse {
		$count = (int) $request->getQueryParam('count', 500);
		$cursor = $request->getQueryParam('cursor', null);
		try {
			$response = $response->writeJsonBody($this->readerManager->getRecords($count, $cursor));
			return $this->validators->validateResponse('journalRecords', $response);
		} catch (JournalReaderArgumentException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S400_BAD_REQUEST);
		} catch (JournalReaderInternalException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

}
