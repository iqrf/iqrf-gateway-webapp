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

namespace App\ApiModule\Version0\Controllers\Security;

use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\OpenApi;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Exception\Api\ServerErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Controllers\BaseController;
use App\ApiModule\Version0\Models\ControllerValidators;
use App\GatewayModule\Exceptions\CertificateNotFoundException;
use App\GatewayModule\Models\CertificateManager;
use LogicException;

/**
 * TLS certificate controller
 */
#[Path('/certificate')]
#[Tag('Security - Certificate management')]
class CertificateController extends BaseController {

	/**
	 * Constructor
	 * @param CertificateManager $manager TLS certificate manager
	 * @param ControllerValidators $validators Controller validators
	 */
	public function __construct(
		private readonly CertificateManager $manager,
		ControllerValidators $validators,
	) {
		parent::__construct($validators);
	}

	#[Path('/')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Returns information about TLS certificate
		deprecated: true
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/CertificateDetail'
			'400':
				$ref: '#/components/responses/Forbidden'
			'500':
				$ref: '#/components/responses/ServerError'
	EOT)]
	public function get(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			$response = $response->writeJsonBody($this->manager->getInfo());
			return $this->validators->validateResponse('certificate', $response);
		} catch (LogicException $e) {
			throw new ServerErrorException('Certificate parsing error', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		} catch (CertificateNotFoundException $e) {
			throw new ServerErrorException('Certificate not found', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

}
