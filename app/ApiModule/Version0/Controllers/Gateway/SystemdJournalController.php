<?php

/**
 * Copyright 2017-2021 MICRORISC s.r.o.
 * Copyright 2017-2021 IQRF Tech s.r.o.
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
use App\GatewayModule\Models\SystemdJournalManager;

/**
 * System journald controller
 * @Path("/journal")
 */
class SystemdJournalController extends GatewayController {

	/**
	 * @var FeatureManager Feature manager
	 */
	private $featureManager;

	/**
	 * @var SystemdJournalManager Systemd journal manager
	 */
	private $manager;

	/**
	 * Constructor
	 * @param FeatureManager $featureManager Feature manager
	 * @param SystemdJournalManager $manager Systemd journal manager
	 * @param RestApiSchemaValidator $validator REST API JSON schema validator
	 */
	public function __construct(FeatureManager $featureManager, SystemdJournalManager $manager, RestApiSchemaValidator $validator) {
		$this->featureManager = $featureManager;
		$this->manager = $manager;
		parent::__construct($validator);
	}

	/**
	 * @Path("/config")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: Returns systemd journal configuration
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/SystemdJournal'
	 *      '500':
	 *          $ref: '#/components/responses/ServerError'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function getConfig(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->featureEnabled();
		try {
			return $response->writeJsonBody($this->manager->getConfig());
		} catch (ConfNotFoundException | InvalidConfFormatException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	/**
	 * @Path("/config")
	 * @Method("POST")
	 * @OpenApi("
	 *  summary: Updates systemd journal configuration
	 *  requestBody:
	 *      required: true
	 *      content:
	 *          application/json:
	 *              schema:
	 *                  $ref: '#/components/schemas/SystemdJournal'
	 *  responses:
	 *      '200':
	 *          description: Success
	 *      '400':
	 *          $ref: '#/components/responses/BadRequest'
	 *      '500':
	 *          $ref: '#/components/responses/ServerError'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function saveConfig(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->featureEnabled();
		$this->validator->validateRequest('systemdJournal', $request);
		try {
			$this->manager->saveConfig($request->getJsonBody(false));
			return $response->writeBody('Workaround');
		} catch (ConfNotFoundException | InvalidConfFormatException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	/**
	 * Checks if systemd journal feature is enabled, and returns bad request if it is not
	 */
	private function featureEnabled(): void {
		if (!$this->featureManager->isEnabled('systemdJournal')) {
			throw new ClientErrorException('Systemd journal feature is not enabled', ApiResponse::S400_BAD_REQUEST);
		}
	}

}
