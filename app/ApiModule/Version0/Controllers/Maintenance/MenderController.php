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

namespace App\ApiModule\Version0\Controllers\Maintenance;

use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\OpenApi;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Exception\Api\ClientErrorException;
use Apitte\Core\Exception\Api\ServerErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Models\ControllerValidators;
use App\ApiModule\Version0\Utils\ContentTypeUtil;
use App\CoreModule\Models\FeatureManager;
use App\MaintenanceModule\Exceptions\MenderFailedException;
use App\MaintenanceModule\Exceptions\MenderInvalidArtifactException;
use App\MaintenanceModule\Exceptions\MenderMissingException;
use App\MaintenanceModule\Exceptions\MenderNoUpdateInProgressException;
use App\MaintenanceModule\Exceptions\MountErrorException;
use App\MaintenanceModule\Models\MenderManager;
use Nette\IOException;

/**
 * Mender maintenance controller
 */
#[Path('/mender')]
#[Tag('Maintenance - Mender')]
class MenderController extends BaseMaintenanceController {

	/**
	 * Constructor
	 * @param FeatureManager $featureManager Feature manager
	 * @param MenderManager $manager Mender client configuration manager
	 * @param ControllerValidators $validators Controller validators
	 */
	public function __construct(
		private readonly FeatureManager $featureManager,
		private readonly MenderManager $manager,
		ControllerValidators $validators,
	) {
		parent::__construct($validators);
	}

	#[Path('/install')]
	#[Method(['POST'])]
	#[OpenApi(<<<'EOT'
		summary: Installs mender artifact
		requestBody:
			required: true
			content:
				multipart/form-data:
					schema:
						type: object
						properties:
							file:
								type: string
								format: binary
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							type: string
							description: Mender action log
			'400':
				$ref: '#/components/responses/BadRequest'
			'403':
				$ref: '#/components/responses/Forbidden'
			'415':
				$ref: '#/components/responses/InvalidContentType'
			'500':
				$ref: '#/components/responses/ServerError'
EOT)]
	public function installArtifact(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['maintenance:mender']);
		ContentTypeUtil::validContentType($request, ['multipart/form-data']);
		try {
			$file = $request->getUploadedFiles()[0];
			$fileName = $file->getClientFilename();
			$this->checkArtifact($fileName);
			$filePath = $this->manager->saveArtifactFile($file);
			return $response->writeBody($this->manager->installArtifact($filePath));
		} catch (MenderInvalidArtifactException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S400_BAD_REQUEST, $e);
		} catch (MenderFailedException | MenderMissingException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		} catch (IOException $e) {
			throw new ServerErrorException('Write failure', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	#[Path('/commit')]
	#[Method('POST')]
	#[OpenApi(<<<'EOT'
		summary: Commits installed mender artifact
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							type: string
							description: Mender action log
			'400':
				$ref: '#/components/responses/BadRequest'
			'403':
				$ref: '#/components/responses/Forbidden'
			'500':
				$ref: '#/components/responses/ServerError'
EOT)]
	public function commitUpdate(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['maintenance:mender']);
		try {
			return $response->writeBody($this->manager->commitUpdate());
		} catch (MenderNoUpdateInProgressException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S400_BAD_REQUEST, $e);
		} catch (MenderMissingException | MenderFailedException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	#[Path('/rollback')]
	#[Method('POST')]
	#[OpenApi(<<<'EOT'
		summary: Rolls installed mender artifact back
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							type: string
							description: Mender action log
			'400':
				$ref: '#/components/responses/BadRequest'
			'403':
				$ref: '#/components/responses/Forbidden'
			'500':
				$ref: '#/components/responses/ServerError'
EOT)]
	public function rollbackUpdate(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['maintenance:mender']);
		try {
			return $response->writeBody($this->manager->rollbackUpdate());
		} catch (MenderNoUpdateInProgressException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S400_BAD_REQUEST, $e);
		} catch (MenderMissingException | MenderFailedException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	#[Path('/remount')]
	#[Method('POST')]
	#[OpenApi(<<<'EOT'
		summary: Remounts root filesystem
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: '#/components/schemas/Remount'
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							type: string
							description: Mender action log
			'400':
				$ref: '#/components/responses/BadRequest'
			'403':
				$ref: '#/components/responses/Forbidden'
			'500':
				$ref: '#/components/responses/ServerError'
EOT)]
	public function remount(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['maintenance:mender']);
		if (!$this->featureManager->isEnabled('remount')) {
			throw new ClientErrorException('Remount feature is not enabled.', ApiResponse::S400_BAD_REQUEST);
		}
		$this->validators->validateRequest('remount', $request);
		try {
			$conf = $request->getJsonBodyCopy();
			$this->manager->remount($conf['mode']);
			return $response;
		} catch (MountErrorException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	/**
	 * Checks if uploaded file is a valid mender artifact file
	 * @param string $fileName File name
	 * @throws MenderInvalidArtifactException
	 */
	private function checkArtifact(string $fileName): void {
		if (!str_ends_with($fileName, '.mender')) {
			throw new MenderInvalidArtifactException('Uploaded file is not a .mender artifact file.');
		}
	}

}
