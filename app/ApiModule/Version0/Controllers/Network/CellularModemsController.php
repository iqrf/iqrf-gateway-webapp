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

namespace App\ApiModule\Version0\Controllers\Network;

use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\OpenApi;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Exception\Api\ServerErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Models\ControllerValidators;
use App\NetworkModule\Exceptions\ModemManagerException;
use App\NetworkModule\Models\CellularManager;

/**
 * Cellular modems controller
 */
#[Path('/modems')]
#[Tag('IP network - Cellular')]
class CellularModemsController extends BaseCellularNetworkController {

	/**
	 * Constructor
	 * @param CellularManager $manager Cellular manager
	 * @param ControllerValidators $validators Controller validators
	 */
	public function __construct(
		private readonly CellularManager $manager,
		ControllerValidators $validators,
	) {
		parent::__construct($validators);
	}

	#[Path('/')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Lists available modems
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
		$this->validators->checkScopes($request, ['network']);
		try {
			$response = $response->writeJsonBody($this->manager->listModems());
			return $this->validators->validateResponse('modemList', $response);
		} catch (ModemManagerException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

	#[Path('/scan')]
	#[Method('POST')]
	#[OpenApi(<<<'EOT'
		summary: Scans for modems
		responses:
			'200':
				description: Success
			'403':
				$ref: '#/components/responses/Forbidden'
			'500':
				$ref: '#/components/responses/ServerError'
	EOT)]
	public function scanModems(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['network']);
		try {
			$this->manager->scanModems();
			return $response;
		} catch (ModemManagerException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

}
