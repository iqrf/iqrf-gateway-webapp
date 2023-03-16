<?php

/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
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
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Exception\Api\ServerErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Models\RestApiSchemaValidator;
use App\GatewayModule\Models\VersionManager;
use Nette\IOException;
use Nette\Utils\JsonException;

/**
 * Version API controller
 */
#[Path('/version')]
#[Tag('Version')]
class VersionController extends BaseController {

	/**
	 * Constructor
	 * @param RestApiSchemaValidator $validator REST API JSON schema validator
	 * @param VersionManager $manager Version manager
	 */
	public function __construct(
		RestApiSchemaValidator $validator,
		private readonly VersionManager $manager,
	) {
		parent::__construct($validator);
	}

	#[Path('/daemon')]
	#[Method('GET')]
	#[OpenApi('
		summary: Returns IQRF Gateway Daemon version
		responses:
			\'200\':
				description: Success
				content:
					application/json:
						schema:
							$ref: \'#/components/schemas/VersionDaemon\'
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
			\'500\':
				$ref: \'#/components/responses/ServerError\'
	')]
	public function daemonVersion(ApiRequest $request, ApiResponse $response): ApiResponse {
		$version = $this->manager->getDaemon();
		if ($version !== 'none' && $version !== 'unknown') {
			return $response->writeJsonBody(['version' => $version]);
		}
		throw new ServerErrorException('IQRF Gateway Daemon not installed', ApiResponse::S500_INTERNAL_SERVER_ERROR);
	}

	#[Path('/webapp')]
	#[Method('GET')]
	#[OpenApi('
		summary: Returns IQRF Gateway Webapp version
		responses:
			\'200\':
				description: Success
				content:
					application/json:
						schema:
							$ref: \'#/components/schemas/VersionWebapp\'
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
			\'500\':
				$ref: \'#/components/responses/ServerError\'
	')]
	public function webappVersion(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			return $response->writeJsonBody($this->manager->getWebappJson());
		} catch (IOException | JsonException $e) {
			throw new ServerErrorException('Invalid JSON syntax', ApiResponse::S500_INTERNAL_SERVER_ERROR, $e);
		}
	}

}
