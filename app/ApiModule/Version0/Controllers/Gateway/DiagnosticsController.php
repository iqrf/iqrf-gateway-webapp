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

use Apitte\Core\Adjuster\FileResponseAdjuster;
use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\OpenApi;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Models\ControllerValidators;
use App\GatewayModule\Models\DiagnosticsManager;
use Nette\Utils\FileSystem;

/**
 * Diagnostics controller
 */
#[Path('/diagnostics')]
#[Tag('Gateway - Information')]
class DiagnosticsController extends BaseGatewayController {

	/**
	 * Constructor
	 * @param DiagnosticsManager $manager Diagnostics manager
	 * @param ControllerValidators $validators Controller validators
	 */
	public function __construct(
		private readonly DiagnosticsManager $manager,
		ControllerValidators $validators,
	) {
		parent::__construct($validators);
	}

	#[Path('/')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Returns archive with diagnostics
		responses:
			'200':
				description: Success
				content:
					application/zip:
						schema:
							type: string
							format: binary
	EOT)]
	public function get(ApiRequest $request, ApiResponse $response): ApiResponse {
		$path = $this->manager->createArchive();
		$fileName = basename($path);
		$response->writeBody(FileSystem::read($path));
		return FileResponseAdjuster::adjust($response, $response->getBody(), $fileName, 'application/zip');
	}

}
