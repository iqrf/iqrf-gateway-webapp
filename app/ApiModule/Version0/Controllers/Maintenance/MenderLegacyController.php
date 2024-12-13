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

namespace App\ApiModule\Version0\Controllers\Maintenance;

use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\OpenApi;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Controllers\BaseController;
use App\ApiModule\Version0\Models\ControllerValidators;

/**
 * Mender client configuration controller
 */
#[Path('/mender')]
#[Tag('Maintenance - Mender')]
class MenderLegacyController extends BaseController {

	/**
	 * Constructor
	 * @param MenderController $newController New Mender controller
	 * @param ControllerValidators $validators Controller validators
	 */
	public function __construct(
		private readonly MenderController $newController,
		ControllerValidators $validators,
	) {
		parent::__construct($validators);
	}

	#[Path('/install')]
	#[Method(['POST'])]
	#[OpenApi(<<<'EOT'
		summary: Installs mender artifact
		deprecated: true
		description: "Deprecated in favor of the new Mender controller, use `POST` `/maintenance/mender/install` instead. Will be removed in the version 3.1.0."
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
		return $this->newController->installArtifact($request, $response);
	}

	#[Path('/commit')]
	#[Method('POST')]
	#[OpenApi(<<<'EOT'
		summary: Commits installed mender artifact
		deprecated: true
		description: "Deprecated in favor of the new Mender controller, use `POST` `/maintenance/mender/commit` instead. Will be removed in the version 3.1.0."
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
		return $this->newController->commitUpdate($request, $response);
	}

	#[Path('/rollback')]
	#[Method('POST')]
	#[OpenApi(<<<'EOT'
		summary: Rolls installed mender artifact back
		deprecated: true
		description: "Deprecated in favor of the new Mender controller, use `POST` `/maintenance/mender/rollback` instead. Will be removed in the version 3.1.0."
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
		return $this->newController->rollbackUpdate($request, $response);
	}

	#[Path('/remount')]
	#[Method('POST')]
	#[OpenApi(<<<'EOT'
		summary: Remounts root filesystem
		deprecated: true
		description: "Deprecated in favor of the new Mender controller, use `POST` `/maintenance/mender/remount` instead. Will be removed in the version 3.1.0."
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
		return $this->newController->remount($request, $response);
	}

}
