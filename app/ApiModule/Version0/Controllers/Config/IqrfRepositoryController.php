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
use App\ConfigModule\Models\IqrfRepositoryManager;
use Nette\IOException;
use Nette\Neon\Exception as NeonException;

/**
 * IQRF Repository controller
 */
#[Path('/iqrfRepository')]
#[Tag('Configuration - IQRF Repository')]
class IqrfRepositoryController extends BaseConfigController {

	/**
	 * Constructor
	 * @param IqrfRepositoryManager $manager IQRF Repository manager
	 * @param ControllerValidators $validators Controller validators
	 */
	public function __construct(
		private readonly IqrfRepositoryManager $manager,
		ControllerValidators $validators,
	) {
		parent::__construct($validators);
	}

	#[Path('/')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Returns the current configuration of IQRF Repository
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/IqrfRepositoryConfig'
			'403':
				$ref: '#/components/responses/Forbidden'
	EOT)]
	public function readConfig(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['config:iqrfRepository']);
		$response = $response->writeJsonBody($this->manager->readConfig());
		return $this->validators->validateResponse('iqrfRepositoryConfig', $response);
	}

	#[Path('/')]
	#[Method('PUT')]
	#[OpenApi(<<<'EOT'
		summary: Updates IQRF repository extension configuration
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: '#/components/schemas/IqrfRepositoryConfig'
		responses:
			'200':
				description: Success
			'400':
				$ref: '#/components/responses/BadRequest'
			'403':
				$ref: '#/components/responses/Forbidden'
			'500':
				$ref: '#/components/responses/ServerError'
	EOT)]
	public function saveConfig(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['config:iqrfRepository']);
		$this->validators->validateRequest('iqrfRepositoryConfig', $request);
		try {
			$config = $request->getJsonBodyCopy();
			$this->manager->saveConfig($config);
			return $response;
		} catch (NeonException | IOException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

}
