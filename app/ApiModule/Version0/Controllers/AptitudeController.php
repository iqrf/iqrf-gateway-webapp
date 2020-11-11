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
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Exception\Api\ServerErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Models\RestApiSchemaValidator;
use App\ConfigModule\Exceptions\AptErrorException;
use App\ConfigModule\Exceptions\AptNotFoundException;
use App\ConfigModule\Models\AptitudeManager;
use Nette\IOException;

/**
 * Aptitude controller
 * @Path("/apt")
 * @Tag("Config manager")
 */
class AptitudeController extends BaseConfigController {

	/**
	 * @var AptitudeManager Aptitude manager
	 */
	private $aptManager;

	/**
	 * Constructor
	 * @param AptitudeManager $aptManager Aptitude manager
	 * @param RestApiSchemaValidator $validator REST API JSON schema validator
	 */
	public function __construct(AptitudeManager $aptManager, RestApiSchemaValidator $validator) {
		$this->aptManager = $aptManager;
		parent::__construct($validator);
	}

	/**
	 * @Path("/")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: Retrieves enabled status of unattended upgrades
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/UnattendedUpgrades'
	 *      '500':
	 *          $ref: '#/components/responses/ServerError'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function getUnattendedUpgrades(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			$result = $this->aptManager->getEnable();
			return $response->writeJsonBody(['enabled' => $result]);
		} catch (AptNotFoundException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR);
		} catch (AptErrorException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR);
		}
	}

	/**
	 * @Path("/")
	 * @Method("POST")
	 * @OpenApi("
	 *  summary: Changes enabled status of unattended upgrades
	 *  requestBody:
	 *      required: true
	 *      content:
	 *          application/json:
	 *              schema:
	 *                  $ref: '#/components/schemas/UnattendedUpgrades'
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/UnattendedUpgrades'
	 *      '400':
	 *          $ref: '#/components/responses/BadRequest'
	 *      '500':
	 *          $ref: '#/components/responses/ServerError'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function changeEnableUnattendedUpgrades(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validator->validateRequest('unattendedUpgrades', $request);
		$setting = $request->getJsonBody();
		try {
			$result = $this->aptManager->setEnable($setting['enabled']);
			return $response->writeJsonBody(['enabled' => $result]);
		} catch (IOException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR);
		}
	}

}
