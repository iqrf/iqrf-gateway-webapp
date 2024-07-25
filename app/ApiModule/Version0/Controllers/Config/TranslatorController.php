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
use App\ConfigModule\Models\TranslatorConfigManager;
use Nette\IOException;
use Nette\Utils\JsonException;

/**
 * IQRF Gateway Translator configuration controller
 */
#[Path('/translator')]
#[Tag('Configuration - IQRF Gateway Translator')]
class TranslatorController extends BaseConfigController {

	/**
	 * Constructor
	 * @param TranslatorConfigManager $manager IQRF Gateway Translator configuration manager
	 * @param ControllerValidators $validators Controller validators
	 */
	public function __construct(
		private readonly TranslatorConfigManager $manager,
		ControllerValidators $validators,
	) {
		parent::__construct($validators);
	}

	#[Path('/')]
	#[Method('GET')]
	#[OpenApi('
		summary: Returns the current configuration of IQRF Gateway Translator
		responses:
			\'200\':
				description: Success
				content:
					application/json:
						schema:
							$ref: \'#/components/schemas/TranslatorConfig\'
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
			\'500\':
				$ref: \'#/components/responses/ServerError\'
	')]
	public function getConfig(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['config:translator']);
		try {
			$config = $this->manager->getConfig();
			$response = $response->writeJsonBody($config);
			return $this->validators->validateResponse('translatorConfig', $response);
		} catch (JsonException $e) {
			throw new ServerErrorException('Invalid JSON syntax', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		} catch (IOException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	#[Path('/')]
	#[Method('PUT')]
	#[OpenApi('
		summary: Updates the configuration of IQRF Gateway Translator
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: \'#/components/schemas/TranslatorConfig\'
		responses:
			\'200\':
				description: Success
			\'400\':
				$ref: \'#/components/responses/BadRequest\'
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
			\'500\':
				$ref: \'#/components/responses/ServerError\'
	')]
	public function setConfig(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['config:translator']);
		$this->validators->validateRequest('translatorConfig', $request);
		try {
			$this->manager->saveConfig($request->getJsonBodyCopy());
			return $response;
		} catch (IOException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

}
