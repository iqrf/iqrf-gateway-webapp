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
use Apitte\Core\Exception\Api\ClientErrorException;
use Apitte\Core\Exception\Api\ServerErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Utils\ContentTypeUtil;
use App\CloudModule\Exceptions\CannotCreateCertificateDirectoryException;
use App\CloudModule\Exceptions\InvalidConnectionStringException;
use App\CloudModule\Exceptions\InvalidPrivateKeyForCertificateException;
use App\CloudModule\Models\AwsManager;
use App\CloudModule\Models\AzureManager;
use App\CloudModule\Models\HexioManager;
use App\CloudModule\Models\IbmCloudManager;
use App\CloudModule\Models\InteliGlueManager;
use App\CoreModule\Exceptions\NonexistentJsonSchemaException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Utils;
use Nette\IOException;
use Nette\Utils\JsonException;
use RuntimeException;

/**
 * Cloud manager controller
 * @Path("/clouds")
 * @Tag("Clouds manager")
 */
class CloudsController extends BaseController {

	/**
	 * @var AwsManager Azure AWS IoT connection manager
	 */
	private $awsManager;

	/**
	 * @var AzureManager Microsoft Azure IoT Hub connection manager
	 */
	private $azureManager;

	/**
	 * @var HexioManager Hexio IoT Platform connection manager
	 */
	private $hexioManager;

	/**
	 * @var IbmCloudManager IBM Cloud IoT connection manager
	 */
	private $ibmCloudManager;

	/**
	 * @var InteliGlueManager Inteliments InteliGlue connection manager
	 */
	private $inteliGlueManager;

	/**
	 * Constructor
	 * @param AwsManager $awsManager Amazon AWS IoT connection manager
	 * @param AzureManager $azureManager Microsoft Azure IoT Hub connection manager
	 * @param HexioManager $hexioManager Hexio IoT Platform connection manager
	 * @param IbmCloudManager $ibmManager IBM CLoud IoT connection manager
	 * @param InteliGlueManager $inteliGlueManager Inteliments InteliGlue connection manager
	 */
	public function __construct(AwsManager $awsManager, AzureManager $azureManager, HexioManager $hexioManager, IbmCloudManager $ibmManager, InteliGlueManager $inteliGlueManager) {
		$this->awsManager = $awsManager;
		$this->azureManager = $azureManager;
		$this->hexioManager = $hexioManager;
		$this->ibmCloudManager = $ibmManager;
		$this->inteliGlueManager = $inteliGlueManager;
	}

	/**
	 * @Path("/aws")
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
	 *      '500':
	 *          $ref: '#/components/responses/ServerError'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function createAws(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			$contentType = ContentTypeUtil::validContentType($request, ['application/json', 'multipart/form-data']);
			if ($contentType === 'application/json') {
				$data = $request->getJsonBody();
			} else {
				$data = $request->getParsedBody();
				$files = $request->getUploadedFiles();
				$data['certificate'] = $files[0]->getStream()->getContents();
				$data['privateKey'] = $files[1]->getStream()->getContents();
			}
			$this->awsManager->createMqttInterface($data);
			return $response->withStatus(ApiResponse::S201_CREATED)
				->withBody(Utils::streamFor());
		} catch (InvalidConnectionStringException $e) {
			throw new ClientErrorException('Invalid connection string', ApiResponse::S400_BAD_REQUEST);
		} catch (NonexistentJsonSchemaException $e) {
			throw new ServerErrorException('Missing JSON schema', ApiResponse::S500_INTERNAL_SERVER_ERROR);
		} catch (IOException $e) {
			throw new ServerErrorException('Write failure', ApiResponse::S500_INTERNAL_SERVER_ERROR);
		} catch (GuzzleException $e) {
			throw new ServerErrorException('Download failure', ApiResponse::S500_INTERNAL_SERVER_ERROR);
		} catch (CannotCreateCertificateDirectoryException $e) {
			throw new ServerErrorException('Certificate directory creation failure', ApiResponse::S500_INTERNAL_SERVER_ERROR);
		} catch (RuntimeException $e) {
			throw new ClientErrorException('Invalid files', ApiResponse::S400_BAD_REQUEST);
		} catch (InvalidPrivateKeyForCertificateException $e) {
			throw new ClientErrorException('The private key does not correspond to the certificate', ApiResponse::S400_BAD_REQUEST);
		}
	}

	/**
	 * @Path("/azure")
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
	 *      '500':
	 *          $ref: '#/components/responses/ServerError'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function createAzure(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			$this->azureManager->createMqttInterface($request->getJsonBody());
			return $response->withStatus(ApiResponse::S201_CREATED)
				->withBody(Utils::streamFor());
		} catch (InvalidConnectionStringException $e) {
			throw new ClientErrorException('Invalid connection string', ApiResponse::S400_BAD_REQUEST);
		} catch (NonexistentJsonSchemaException $e) {
			throw new ClientErrorException('Missing JSON schema', ApiResponse::S400_BAD_REQUEST);
		} catch (IOException $e) {
			throw new ServerErrorException('Write failure', ApiResponse::S500_INTERNAL_SERVER_ERROR);
		} catch (GuzzleException $e) {
			throw new ServerErrorException('Download failure', ApiResponse::S500_INTERNAL_SERVER_ERROR);
		} catch (CannotCreateCertificateDirectoryException $e) {
			throw new ServerErrorException('Certificate directory creation failure', ApiResponse::S500_INTERNAL_SERVER_ERROR);
		} catch (JsonException $e) {
			throw new ClientErrorException('Invalid JSON syntax', ApiResponse::S400_BAD_REQUEST);
		}
	}

	/**
	 * @Path("/hexio")
	 * @Method("POST")
	 * @OpenApi("
	 *  summary: Creates a new MQTT connection into Hexio IoT Platform
	 *  requestBody:
	 *      description: Hexio IoT Platform connection configuration
	 *      required: true
	 *      content:
	 *          application/json:
	 *              schema:
	 *                  $ref: '#/components/schemas/CloudHexio'
	 *  responses:
	 *      '201':
	 *          description: Created
	 *      '400':
	 *          $ref: '#/components/responses/BadRequest'
	 *      '500':
	 *          $ref: '#/components/responses/ServerError'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function createHexio(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			$this->hexioManager->createMqttInterface($request->getJsonBody());
			return $response->withStatus(ApiResponse::S201_CREATED)
				->withBody(Utils::streamFor());
		} catch (NonexistentJsonSchemaException $e) {
			throw new ServerErrorException('Missing JSON schema', ApiResponse::S500_INTERNAL_SERVER_ERROR);
		} catch (IOException $e) {
			throw new ServerErrorException('Write failure', ApiResponse::S500_INTERNAL_SERVER_ERROR);
		} catch (GuzzleException $e) {
			throw new ServerErrorException('Download failure', ApiResponse::S500_INTERNAL_SERVER_ERROR);
		} catch (CannotCreateCertificateDirectoryException $e) {
			throw new ServerErrorException('Certificate directory creation failure', ApiResponse::S500_INTERNAL_SERVER_ERROR);
		} catch (JsonException $e) {
			throw new ClientErrorException('Invalid JSON syntax', ApiResponse::S400_BAD_REQUEST);
		}
	}

	/**
	 * @Path("/ibmCloud")
	 * @Method("POST")
	 * @OpenApi("
	 *  summary: Creates a new MQTT connection into IBM Cloud
	 *  requestBody:
	 *      description: IBM Cloud connection configuration
	 *      required: true
	 *      content:
	 *          application/json:
	 *              schema:
	 *                  $ref: '#/components/schemas/CloudIbm'
	 *  responses:
	 *      '201':
	 *          description: Created
	 *      '400':
	 *          $ref: '#/components/responses/BadRequest'
	 *      '500':
	 *          $ref: '#/components/responses/ServerError'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function createIbmCloud(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			$this->ibmCloudManager->createMqttInterface($request->getJsonBody());
			return $response->withStatus(ApiResponse::S201_CREATED)
				->withBody(Utils::streamFor());
		} catch (NonexistentJsonSchemaException $e) {
			throw new ServerErrorException('Missing JSON schema', ApiResponse::S500_INTERNAL_SERVER_ERROR);
		} catch (IOException $e) {
			throw new ServerErrorException('Write failure', ApiResponse::S500_INTERNAL_SERVER_ERROR);
		} catch (GuzzleException $e) {
			throw new ServerErrorException('Download failure', ApiResponse::S500_INTERNAL_SERVER_ERROR);
		} catch (CannotCreateCertificateDirectoryException $e) {
			throw new ServerErrorException('Certificate directory creation failure', ApiResponse::S500_INTERNAL_SERVER_ERROR);
		} catch (JsonException $e) {
			throw new ClientErrorException('Invalid JSON syntax', ApiResponse::S400_BAD_REQUEST);
		}
	}

	/**
	 * @Path("/inteliGlue")
	 * @Method("POST")
	 * @OpenApi("
	 *  summary: Creates a new MQTT connection into Inteliments InteliGlue
	 *  requestBody:
	 *      description: Inteliments InteliGlue connection configuration
	 *      required: true
	 *      content:
	 *          application/json:
	 *              schema:
	 *                  $ref: '#/components/schemas/CloudInteliGlue'
	 *  responses:
	 *      '201':
	 *          description: Created
	 *      '400':
	 *          $ref: '#/components/responses/BadRequest'
	 *      '500':
	 *          $ref: '#/components/responses/ServerError'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function createInteliGlue(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			$this->inteliGlueManager->createMqttInterface($request->getJsonBody());
			return $response->withStatus(ApiResponse::S201_CREATED)
				->withBody(Utils::streamFor());
		} catch (NonexistentJsonSchemaException $e) {
			throw new ServerErrorException('Missing JSON schema', ApiResponse::S500_INTERNAL_SERVER_ERROR);
		} catch (IOException $e) {
			throw new ServerErrorException('Write failure', ApiResponse::S500_INTERNAL_SERVER_ERROR);
		} catch (GuzzleException $e) {
			throw new ServerErrorException('Download failure', ApiResponse::S500_INTERNAL_SERVER_ERROR);
		} catch (CannotCreateCertificateDirectoryException $e) {
			throw new ServerErrorException('Certificate directory creation failure', ApiResponse::S500_INTERNAL_SERVER_ERROR);
		} catch (JsonException $e) {
			throw new ClientErrorException('Invalid JSON syntax', ApiResponse::S400_BAD_REQUEST);
		}
	}

}
