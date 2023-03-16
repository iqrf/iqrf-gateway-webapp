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

namespace App\ApiModule\Version0\Controllers\Gateway;

use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\OpenApi;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Exception\Api\ClientErrorException;
use Apitte\Core\Exception\Api\ServerErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Controllers\GatewayController;
use App\ApiModule\Version0\Models\RestApiSchemaValidator;
use App\CoreModule\Models\FeatureManager;
use App\GatewayModule\Exceptions\ConfNotFoundException;
use App\GatewayModule\Exceptions\InvalidConfFormatException;
use App\GatewayModule\Exceptions\JournalReaderArgumentException;
use App\GatewayModule\Exceptions\JournalReaderInternalException;
use App\GatewayModule\Models\JournalConfigManager;
use App\GatewayModule\Models\JournalReaderManager;

/**
 * Journal controller
 */
#[Path('/journal')]
class JournalController extends GatewayController {

	/**
	 * Constructor
	 * @param FeatureManager $featureManager Feature manager
	 * @param JournalConfigManager $configManager Journal config manager
	 * @param JournalReaderManager $readerManager Journal reader manager
	 * @param RestApiSchemaValidator $validator REST API JSON schema validator
	 */
	public function __construct(
		private readonly FeatureManager $featureManager,
		private readonly JournalConfigManager $configManager,
		private readonly JournalReaderManager $readerManager,
		RestApiSchemaValidator $validator,
	) {
		parent::__construct($validator);
	}

	#[Path('/config')]
	#[Method('GET')]
	#[OpenApi('
		summary: Returns journal configuration
		responses:
			\'200\':
				description: Success
				content:
					application/json:
						schema:
							$ref: \'#/components/schemas/Journal\'
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
			\'500\':
				$ref: \'#/components/responses/ServerError\'
	')]
	public function getConfig(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->featureEnabled();
		try {
			return $response->writeJsonBody($this->configManager->getConfig());
		} catch (ConfNotFoundException | InvalidConfFormatException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	#[Path('/config')]
	#[Method('POST')]
	#[OpenApi('
		summary: Updates journal configuration
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: \'#/components/schemas/Journal\'
		responses:
			\'200\':
				description: Success
			\'400\':
				$ref: \'#/components/responses/BadRequest\'
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
			\'500\':
				$ref: \'#/components/responses/ServerError\'
	')]
	public function saveConfig(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->featureEnabled();
		$this->validator->validateRequest('journal', $request);
		try {
			$this->configManager->saveConfig($request->getJsonBodyCopy(false));
			return $response->writeBody('Workaround');
		} catch (ConfNotFoundException | InvalidConfFormatException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	#[Path('/')]
	#[Method('GET')]
	#[OpenApi('
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
			\'200\':
				description: Success
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
			\'500\':
				$ref: \'#/components/responses/ServerError\'
	')]
	public function get(ApiRequest $request, ApiResponse $response): ApiResponse {
		$count = (int) $request->getQueryParam('count', 500);
		$cursor = $request->getQueryParam('cursor', null);
		try {
			return $response->writeJsonBody($this->readerManager->getRecords($count, $cursor));
		} catch (JournalReaderArgumentException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S400_BAD_REQUEST);
		} catch (JournalReaderInternalException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	/**
	 * Checks if journal feature is enabled, and returns bad request if it is not
	 */
	private function featureEnabled(): void {
		if (!$this->featureManager->isEnabled('journal')) {
			throw new ClientErrorException('Journal feature is not enabled', ApiResponse::S400_BAD_REQUEST);
		}
	}

}
