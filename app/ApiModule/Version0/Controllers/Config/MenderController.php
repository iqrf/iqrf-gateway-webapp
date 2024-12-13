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

namespace App\ApiModule\Version0\Controllers\Config;

use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\OpenApi;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Exception\Api\ServerErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Models\ControllerValidators;
use App\MaintenanceModule\Exceptions\MenderUnsupportedVersionException;
use App\MaintenanceModule\Models\MenderManager;
use Nette\IOException;
use Nette\Utils\JsonException;

/**
 * Mender client configuration controller
 */
#[Path('/mender')]
#[Tag('Configuration - Mender')]
class MenderController extends BaseConfigController {

	/**
	 * Constructor
	 * @param MenderManager $manager Mender client configuration manager
	 * @param ControllerValidators $validators Controller validators
	 */
	public function __construct(
		private readonly MenderManager $manager,
		ControllerValidators $validators,
	) {
		parent::__construct($validators);
	}

	#[Path('/')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Returns current configuration of Mender client
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/MenderConfig'
			'403':
				$ref: '#/components/responses/Forbidden'
			'500':
				$ref: '#/components/responses/ServerError'
			'501':
				description: Unsupported Mender client version
	EOT)]
	public function getConfig(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['maintenance:mender']);
		try {
			$config = $this->manager->getConfig();
			$response = $response->writeJsonBody($config);
			return $this->validators->validateResponse('menderConfig', $response);
		} catch (JsonException $e) {
			throw new ServerErrorException('Invalid JSON Syntax', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		} catch (IOException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		} catch (MenderUnsupportedVersionException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S501_NOT_IMPLEMENTED, $e);
		}
	}

	#[Path('/')]
	#[Method('PUT')]
	#[OpenApi(<<<'EOT'
		summary: Saves new Mender client configuration
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: '#/components/schemas/MenderConfig'
		responses:
			'200':
				description: Success
			'400':
				$ref: '#/components/responses/BadRequest'
			'403':
				$ref: '#/components/responses/Forbidden'
			'500':
				$ref: '#/components/responses/ServerError'
			'501':
				description: Unsupported Mender client version
	EOT)]
	public function setConfig(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['maintenance:mender']);
		$this->validators->validateRequest('menderConfig', $request);
		try {
			$this->manager->saveConfig($request->getJsonBodyCopy());
			return $response;
		} catch (IOException | JsonException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		} catch (MenderUnsupportedVersionException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S501_NOT_IMPLEMENTED, $e);
		}
	}

	#[Path('/cert')]
	#[Method('POST')]
	#[OpenApi(<<<'EOT'
		summary: Uploads and stores a Mender server certificate
		requestBody:
			required: true
			content:
				multipart/form-data:
					schema:
						type: object
						properties:
							certificate:
								type: string
								format: binary

		responses:
			'201':
				description: Created
				content:
					text/plain:
						schema:
							type: string
							description: Path to the stored certificate
			'400':
				$ref: '#/components/responses/BadRequest'
			'403':
				$ref: '#/components/responses/Forbidden'
			'500':
				$ref: '#/components/responses/ServerError'
	EOT)]
	public function uploadCert(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['maintenance:mender']);
		try {
			$file = $request->getUploadedFiles()[0];
			$fileName = $file->getClientFilename();
			$content = $file->getStream()->getContents();
			$filePath = $this->manager->saveCertFile($fileName, $content);
			return $response->withStatus(ApiResponse::S201_CREATED)
				->withHeader('Content-Type', 'text/plain')
				->writeBody($filePath);
		} catch (IOException $e) {
			throw new ServerErrorException('Write failure', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

}
