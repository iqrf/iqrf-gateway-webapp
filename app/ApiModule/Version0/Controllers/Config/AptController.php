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
use App\ConfigModule\Exceptions\AptErrorException;
use App\ConfigModule\Exceptions\AptNotFoundException;
use App\ConfigModule\Models\AptManager;
use Nette\IOException;

/**
 * APT configuration controller
 */
#[Path('/apt')]
#[Tag('Configuration - APT package manager')]
class AptController extends BaseConfigController {

	/**
	 * Constructor
	 * @param AptManager $manager APT manager
	 * @param ControllerValidators $validators Controller validators
	 */
	public function __construct(
		private readonly AptManager $manager,
		ControllerValidators $validators,
	) {
		parent::__construct($validators);
	}

	#[Path('/')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Returns APT configuration
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/AptConfiguration'
			'403':
				$ref: '#/components/responses/Forbidden'
			'500':
				$ref: '#/components/responses/ServerError'
	EOT)]
	public function read(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			$response = $response->writeJsonBody($this->manager->read());
			return $this->validators->validateResponse('aptConfiguration', $response);
		} catch (AptErrorException | AptNotFoundException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	#[Path('/')]
	#[Method('PUT')]
	#[OpenApi(<<<'EOT'
		summary: Updates APT configuration
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: '#/components/schemas/AptConfiguration'
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
	public function changeEnableUnattendedUpgrades(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->validateRequest('aptConfiguration', $request);
		try {
			$this->manager->write($request->getJsonBodyCopy());
			return $response;
		} catch (AptErrorException | AptNotFoundException | IOException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

}
