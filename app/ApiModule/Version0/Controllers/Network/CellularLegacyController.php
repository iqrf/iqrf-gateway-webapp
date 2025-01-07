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

namespace App\ApiModule\Version0\Controllers\Network;

use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\OpenApi;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Models\ControllerValidators;

/**
 * Cellular controller
 */
#[Path('/gsm')]
#[Tag('IP network - Cellular')]
class CellularLegacyController extends BaseNetworkController {

	/**
	 * Constructor
	 * @param CellularModemsController $newController New cellular controller
	 * @param ControllerValidators $validators Controller validators
	 */
	public function __construct(
		private readonly CellularModemsController $newController,
		ControllerValidators $validators,
	) {
		parent::__construct($validators);
	}

	#[Path('/modems')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Lists available modems
		deprecated: true
		description: "Deprecated in favor of the new Cellular controller, use `GET` `/network/cellular/modems` instead. Will be removed in the version 3.1.0."
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/ModemList'
			'403':
				$ref: '#/components/responses/Forbidden'
			'500':
				$ref: '#/components/responses/ServerError'
	EOT)]
	public function listModems(ApiRequest $request, ApiResponse $response): ApiResponse {
		$response = $this->newController->listModems($request, $response);
		return $this->validators->validateResponse('modemList', $response);
	}

	#[Path('/modems/scan')]
	#[Method('POST')]
	#[OpenApi(<<<'EOT'
		summary: Scans for modems
		deprecated: true
		description: "Deprecated in favor of the new Cellular controller, use `POST` `/network/cellular/modems/scan` instead. Will be removed in the version 3.1.0."
		responses:
			'200':
				description: Success
			'403':
				$ref: '#/components/responses/Forbidden'
			'500':
				$ref: '#/components/responses/ServerError'
	EOT)]
	public function scanModems(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $this->newController->scanModems($request, $response);
	}

}
