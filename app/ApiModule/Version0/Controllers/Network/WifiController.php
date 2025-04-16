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
use Apitte\Core\Exception\Api\ServerErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Models\ControllerValidators;
use App\NetworkModule\Exceptions\NetworkManagerException;
use App\NetworkModule\Models\WifiManager;

/**
 * WiFi controller
 */
#[Path('/wifi')]
#[Tag('IP network - WiFi')]
class WifiController extends BaseNetworkController {

	/**
	 * Constructor
	 * @param WifiManager $manager WiFi network manager
	 * @param ControllerValidators $validators Controller validators
	 */
	public function __construct(
		private readonly WifiManager $manager,
		ControllerValidators $validators,
	) {
		parent::__construct($validators);
	}

	#[Path('/list')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Lists available WiFi access points
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/NetworkWifiList'
			'403':
				$ref: '#/components/responses/Forbidden'
			'500':
				$ref: '#/components/responses/ServerError'
EOT)]
	public function list(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['network']);
		try {
			$response = $response->writeJsonBody($this->manager->list());
			return $this->validators->validateResponse('networkWifiList', $response);
		} catch (NetworkManagerException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

}
