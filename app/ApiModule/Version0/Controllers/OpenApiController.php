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
use Apitte\OpenApi\ISchemaBuilder;
use Nette\Utils\Strings;
use stdClass;

/**
 * OpenAPI controller
 * @Path("/openapi")
 * @Tag("OpenAPI")
 */
class OpenApiController extends BaseController {

	/**
	 * @var ISchemaBuilder OpenAPI schema builder
	 */
	private $schemaBuilder;

	/**
	 * Constructor
	 * @param ISchemaBuilder $schemaBuilder OpenAPI schema builder
	 */
	public function __construct(ISchemaBuilder $schemaBuilder) {
		$this->schemaBuilder = $schemaBuilder;
	}

	/**
	 * @Path("/")
	 * @Method("GET")
	 * @OpenApi("
	 *   summary: Returns OpenAPI schema
	 *   security:
	 *     - []
	 * ")
	 * @Responses({
	 *      @Response(code="200", description="Success")
	 * })
	 */
	public function index(ApiRequest $request, ApiResponse $response): ApiResponse {
		$openApi = $this->schemaBuilder->build()->toArray();
		$openApi['paths']['/api/v0/openapi']['get']['security'] = [new stdClass()];
		$openApi['paths']['/api/v0/user/signIn']['post']['security'] = [new stdClass()];
		foreach ($openApi['servers'] as &$server) {
			$server['url'] .= 'api/v0/';
		}
		foreach ($openApi['paths'] as $uri => $path) {
			$openApi['paths'][Strings::replace($uri, '~/api/v0~', '')] = $path;
			unset($openApi['paths'][$uri]);
		}
		return $response->writeJsonBody($openApi);
	}

}
