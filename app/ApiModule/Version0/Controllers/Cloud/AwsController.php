<?php

/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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
use App\ApiModule\Version0\Utils\ContentTypeUtil;
use App\CloudModule\Exceptions\CannotCreateCertificateDirectoryException;
use App\CloudModule\Exceptions\InvalidPrivateKeyForCertificateException;
use App\CloudModule\Models\AwsManager;
use App\CoreModule\Exceptions\NonexistentJsonSchemaException;
use GuzzleHttp\Exception\GuzzleException;
use Nette\IOException;
use RuntimeException;

/**
 * Amazon AWS IoT connection controller
 * @Path("/aws")
 */
class AwsController extends CloudsController {

	/**
	 * Constructor
	 * @param AwsManager $manager Amazon AWS IoT connection manager
	 * @param RestApiSchemaValidator $validator REST API JSON schema validator
	 */
	public function __construct(AwsManager $manager, RestApiSchemaValidator $validator) {
		$this->manager = $manager;
		parent::__construct($validator);
	}

	/**
	 * @Path("/")
	 * @Method("POST")
	 * @OpenApi("
	 *  summary: Creates a new MQTT connection into Amazon AWS IoT
	 *  requestBody:
	 *      description: Amazon AWS IoT connection configuration
	 *      required: true
	 *      content:
	 *          application/json:
	 *              schema:
	 *                  $ref: '#/components/schemas/CloudAws'
	 *          multipart/form-data:
	 *              schema:
	 *                  type: object
	 *                  properties:
	 *                      endpoint:
	 *                          type: string
	 *                      certificate:
	 *                          type: string
	 *                          format: binary
	 *                      privateKey:
	 *                          type: string
	 *                          format: binary
	 *
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
		try {
			$configuration = $this->getConfiguration($request);
			$this->manager->createMqttInterface($configuration);
			return $response->withStatus(ApiResponse::S201_CREATED)
				->writeBody('Workaround');
		} catch (NonexistentJsonSchemaException $e) {
			throw new ServerErrorException('Missing JSON schema', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		} catch (IOException $e) {
			throw new ServerErrorException('Write failure', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		} catch (GuzzleException $e) {
			throw new ServerErrorException('Download failure', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		} catch (CannotCreateCertificateDirectoryException $e) {
			throw new ServerErrorException('Failed to create certificate directory', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		} catch (RuntimeException $e) {
			throw new ClientErrorException('Invalid files', ApiResponse::S400_BAD_REQUEST, $e);
		} catch (InvalidPrivateKeyForCertificateException $e) {
			throw new ClientErrorException('The private key does not correspond to the certificate', ApiResponse::S400_BAD_REQUEST, $e);
		}
	}

	/**
	 * Returns Amazon AWS IoT connection configuration
	 * @param ApiRequest $request API request
	 * @return array<string, int|string> Amazon AWS IoT connection configuration
	 */
	private function getConfiguration(ApiRequest $request): array {
		$contentType = ContentTypeUtil::validContentType($request, ['application/json', 'multipart/form-data']);
		if ($contentType === 'application/json') {
			$this->validator->validateRequest('cloudAws', $request);
			return $request->getJsonBody();
		}
		$data = $request->getParsedBody();
		$files = $request->getUploadedFiles();
		$data['certificate'] = $files[0]->getStream()->getContents();
		$data['privateKey'] = $files[1]->getStream()->getContents();
		return $data;
	}

}
