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
use Apitte\Core\Annotation\Controller\Response;
use Apitte\Core\Annotation\Controller\Responses;
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;

/**
 * Version API controller
 * @Path("/version")
 * @Tag("Version")
 */
class VersionController extends BaseController {

	/**
	 * @Path("/")
	 * @Method("GET")
	 * @OpenApi("
	 *   summary: Returns IQRF Gateway Webapp version
	 * ")
	 * @Responses({
	 *      @Response(code="200", description="Success", entity="\App\ApiModule\Version0\Entities\Response\VersionEntity")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function index(ApiRequest $request, ApiResponse $response): ApiResponse {
		$json = Json::decode(FileSystem::read(__DIR__ . '/../../../../version.json'), Json::FORCE_ARRAY);
		$response = $response->writeJsonBody($json);
		return $response;
	}

}
