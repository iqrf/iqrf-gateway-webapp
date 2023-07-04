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

namespace App\ApiModule\Version0\Controllers\Config;

use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\OpenApi;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Exception\Api\ServerErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Controllers\BaseConfigController;
use App\ApiModule\Version0\Models\RestApiSchemaValidator;
use App\ConfigModule\Models\IqrfRepositoryManager;
use Nette\IOException;
use Nette\Neon\Exception as NeonException;

/**
 * IQRF Repository controller
 * @Path("/iqrf-repository")
 * @Tag("IQRF Repository configuration")
 */
class IqrfRepositoryController extends BaseConfigController {

	/**
	 * @var IqrfRepositoryManager IQRF Repository manager
	 */
	private IqrfRepositoryManager $manager;

	/**
	 * Constructor
	 * @param IqrfRepositoryManager $manager IQRF Repository manager
	 * @param RestApiSchemaValidator $validator REST API JSON schema validator
	 */
	public function __construct(IqrfRepositoryManager $manager, RestApiSchemaValidator $validator) {
		$this->manager = $manager;
		parent::__construct($validator);
	}

	/**
	 * @Path("/")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: Returns IQRF repository extension configuration
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/IqrfRepositoryConfig'
	 *      '403':
	 *          $ref: '#/components/responses/Forbidden'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function readConfig(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['config:iqrfRepository']);
		return $response->writeJsonBody($this->manager->readConfig());
	}

	/**
	 * @Path("/")
	 * @Method("PUT")
	 * @OpenApi("
	 *  summary: Updates IQRF repository extension configuration
	 *  requestBody:
	 *      required: true
	 *      content:
	 *          application/json:
	 *              schema:
	 *                  $ref: '#/components/schemas/IqrfRepositoryConfig'
	 *  responses:
	 *      '200':
	 *          description: Success
	 *      '400':
	 *          $ref: '#/components/responses/BadRequest'
	 *      '403':
	 *          $ref: '#/components/responses/Forbidden'
	 *      '500':
	 *          $ref: '#/components/responses/ServerError'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function saveConfig(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['config:iqrfRepository']);
		$this->validator->validateRequest('iqrfRepositoryConfig', $request);
		try {
			$config = $request->getJsonBodyCopy(true);
			$this->manager->saveConfig($config);
			return $response->writeBody('Workaround');
		} catch (NeonException | IOException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

}
