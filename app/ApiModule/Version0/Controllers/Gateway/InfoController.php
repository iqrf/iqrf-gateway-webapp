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

namespace App\ApiModule\Version0\Controllers\Gateway;

use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\OpenApi;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Models\ControllerValidators;
use App\GatewayModule\Models\InfoManager;

/**
 * Gateway information controller
 */
#[Path('/info')]
#[Tag('Gateway - Information')]
class InfoController extends BaseGatewayController {

	/**
	 * Constructor
	 * @param InfoManager $infoManager Gateway info manager
	 * @param ControllerValidators $validators Controller validators
	 */
	public function __construct(
		private readonly InfoManager $infoManager,
		ControllerValidators $validators,
	) {
		parent::__construct($validators);
	}

	#[Path('/')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Returns information about the gateway
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/GatewayInfo'
			'403':
				$ref: '#/components/responses/Forbidden'
	EOT)]
	public function get(ApiRequest $request, ApiResponse $response): ApiResponse {
		$info = $this->infoManager->get();
		$response = $response->writeJsonBody($info);
		return $this->validators->validateResponse('gatewayInfo', $response);
	}

	#[Path('/brief')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Returns brief information about the gateway
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/GatewayBriefInfo'
			'403':
				$ref: '#/components/responses/Forbidden'
	EOT)]
	public function getBrief(ApiRequest $request, ApiResponse $response): ApiResponse {
		$info = $this->infoManager->getBrief();
		$response = $response->writeJsonBody($info);
		return $this->validators->validateResponse('gatewayBriefInfo', $response);
	}

}
