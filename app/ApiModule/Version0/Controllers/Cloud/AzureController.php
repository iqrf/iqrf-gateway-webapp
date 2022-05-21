<?php

/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
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

namespace App\ApiModule\Version0\Controllers\Cloud;

use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\OpenApi;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Exception\Api\ClientErrorException;
use Apitte\Core\Exception\Api\ServerErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Controllers\CloudsController;
use App\ApiModule\Version0\Models\RestApiSchemaValidator;
use App\CloudModule\Exceptions\CannotCreateCertificateDirectoryException;
use App\CloudModule\Exceptions\InvalidConnectionStringException;
use App\CloudModule\Models\AzureManager;
use App\CoreModule\Exceptions\NonexistentJsonSchemaException;
use Nette\IOException;

/**
 * Microsoft Azure IoT Hub connection controller
 * @Path("/azure")
 */
class AzureController extends CloudsController {

	/**
	 * @var AzureManager Microsoft Azure IoT Hub connection manager
	 */
	private AzureManager $manager;

	/**
	 * Constructor
	 * @param AzureManager $manager Microsoft Azure IoT Hub connection manager
	 * @param RestApiSchemaValidator $validator REST API JSON schema validator
	 */
	public function __construct(AzureManager $manager, RestApiSchemaValidator $validator) {
		$this->manager = $manager;
		parent::__construct($validator);
	}

	/**
	 * @Path("/")
	 * @Method("POST")
	 * @OpenApi("
	 *  summary: Creates a new MQTT connection into Microsoft Azure IoT Hub
	 *  requestBody:
	 *      description: Microsoft Azure IoT Hub connection configuration
	 *      required: true
	 *      content:
	 *          application/json:
	 *              schema:
	 *                  $ref: '#/components/schemas/CloudAzure'
	 *  responses:
	 *      '201':
	 *          description: Created
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
	public function create(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['clouds']);
		$this->validator->validateRequest('cloudAzure', $request);
		try {
			$this->manager->createMqttInterface($request->getJsonBody());
			return $response->withStatus(ApiResponse::S201_CREATED)
				->writeBody('Workaround');
		} catch (InvalidConnectionStringException $e) {
			throw new ClientErrorException('Invalid connection string', ApiResponse::S400_BAD_REQUEST, $e);
		} catch (NonexistentJsonSchemaException $e) {
			throw new ClientErrorException('Missing JSON schema', ApiResponse::S400_BAD_REQUEST, $e);
		} catch (IOException $e) {
			throw new ServerErrorException('Write failure', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		} catch (CannotCreateCertificateDirectoryException $e) {
			throw new ServerErrorException('Failed to create certificate directory', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

}
