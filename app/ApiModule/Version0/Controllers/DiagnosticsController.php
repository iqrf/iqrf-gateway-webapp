<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2019 IQRF Tech s.r.o.
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
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\GatewayModule\Models\DiagnosticsManager;
use Nette\Utils\FileSystem;

/**
 * Diagnostics controller
 * @Path("/diagnostics")
 * @Tag("Gateway manager")
 */
class DiagnosticsController extends BaseController {

	/**
	 * @var DiagnosticsManager Diagnostics manager
	 */
	private $manager;

	/**
	 * Constructor
	 * @param DiagnosticsManager $manager Diagnostics manager
	 */
	public function __construct(DiagnosticsManager $manager) {
		$this->manager = $manager;
	}

	/**
	 * @Path("/")
	 * @Method("GET")
	 * @OpenApi("
	 *   summary: Returns archive with diagnostics
	 *   responses:
	 *     '200':
	 *       description: 'Success'
	 *       content:
	 *         application/zip:
	 *           schema:
	 *             type: file
	 *             format: binary
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function get(ApiRequest $request, ApiResponse $response): ApiResponse {
		$path = $this->manager->createArchive();
		$fileName = basename($path);
		$headers = [
			'Content-Type' => 'application/zip',
			'Content-Disposition' => 'attachment; filename="' . $fileName . '"; filename*=utf-8\'\'' . rawurlencode($fileName),
			'Content-Length' => (string) filesize($path),
		];
		return $response->withHeaders($headers)
			->writeBody(FileSystem::read($path));
	}

}
