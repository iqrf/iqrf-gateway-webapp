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

namespace App\ApiModule\Version0\Controllers\Iqrf;

use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\OpenApi;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Models\ControllerValidators;
use App\ConfigModule\Models\IqrfManager;

/**
 * IQRF physical interface controller
 */
#[Path('/interfaces')]
class InterfacesController extends BaseIqrfController {

	/**
	 * Constructor
	 * @param IqrfManager $manager IQRF interfaces manager
	 * @param ControllerValidators $validators Controller validators
	 */
	public function __construct(
		private readonly IqrfManager $manager,
		ControllerValidators $validators,
	) {
		parent::__construct($validators);
	}

	#[Path('/')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Returns IQRF interfaces
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/IqrfInterfaces'
			'403':
				$ref: '#/components/responses/Forbidden'
	EOT)]
	public function list(ApiRequest $request, ApiResponse $response): ApiResponse {
		$interfaces = [
			'cdc' => $this->manager->getCdcInterfaces(),
			'spi' => $this->manager->getSpiInterfaces(),
			'uart' => $this->manager->getUartInterfaces(),
		];
		$response = $response->writeJsonBody($interfaces);
		return $this->validators->validateResponse('iqrfInterfaces', $response);
	}

}
