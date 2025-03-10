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

namespace App\ApiModule\Version0\Controllers;

use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\OpenApi;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Annotation\Controller\RequestParameter;
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Exception\Api\ClientErrorException;
use Apitte\Core\Exception\Api\ServerErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Models\ControllerValidators;
use App\CoreModule\Exceptions\FeatureNotFoundException;
use App\CoreModule\Models\FeatureManager;
use Nette\IOException;

/**
 * Optional feature manager controller
 */
#[Path('/features')]
#[Tag('Optional feature manager')]
class FeatureController extends BaseController {

	/**
	 * Constructor
	 * @param FeatureManager $manager Optional feature manager
	 * @param ControllerValidators $validators Controller validators
	 */
	public function __construct(
		private readonly FeatureManager $manager,
		ControllerValidators $validators,
	) {
		parent::__construct($validators);
	}

	#[Path('/')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Returns optional features configuration
		security:
			- []
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/FeatureList'
			'500':
				$ref: '#/components/responses/ServerError'
	EOT)]
	public function getAll(ApiRequest $request, ApiResponse $response): ApiResponse {
		$config = $this->manager->read();
		$response = $response->writeJsonBody($config);
		return $this->validators->validateResponse('featureList', $response);
	}

	#[Path('/{feature}')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Returns optional feature configuration
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/Feature'
			'403':
				$ref: '#/components/responses/Forbidden'
			'404':
				$ref: '#/components/responses/NotFound'
			'500':
				$ref: '#/components/responses/ServerError'
	EOT)]
	#[RequestParameter(name: 'feature', type: 'string', description: 'Feature name')]
	public function get(ApiRequest $request, ApiResponse $response): ApiResponse {
		$name = urldecode($request->getParameter('feature'));
		try {
			$response = $response->writeJsonBody($this->manager->get($name));
			return $this->validators->validateResponse('features/' . $name, $response);
		} catch (FeatureNotFoundException $e) {
			throw new ClientErrorException('Feature not found', ApiResponse::S404_NOT_FOUND, $e);
		}
	}

	#[Path('/{feature}')]
	#[Method('PUT')]
	#[OpenApi(<<<'EOT'
		summary: Edits optional feature configuration
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: '#/components/schemas/Feature'
		responses:
			'200':
				description: Success
			'400':
				$ref: '#/components/responses/BadRequest'
			'403':
				$ref: '#/components/responses/Forbidden'
			'404':
				$ref: '#/components/responses/NotFound'
			'500':
				$ref: '#/components/responses/ServerError'
	EOT)]
	#[RequestParameter(name: 'feature', type: 'string', description: 'Feature name')]
	public function edit(ApiRequest $request, ApiResponse $response): ApiResponse {
		$name = urldecode($request->getParameter('feature'));
		if (!$this->manager->existsDefault($name)) {
			throw new ClientErrorException('Feature not found', ApiResponse::S404_NOT_FOUND);
		}
		$this->validators->validateRequest('features/' . $name, $request);
		try {
			$this->manager->edit($name, $request->getJsonBodyCopy());
			return $response;
		} catch (FeatureNotFoundException $e) {
			throw new ClientErrorException('Feature not found', ApiResponse::S404_NOT_FOUND, $e);
		} catch (IOException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

}
