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
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Models\OpenApiSchemaBuilder;
use App\ApiModule\Version0\Models\RestApiSchemaValidator;

/**
 * OpenAPI controller
 * @Path("/openapi")
 * @Tag("OpenAPI")
 */
class OpenApiController extends BaseController {

	/**
	 * @var OpenApiSchemaBuilder OpenAPI schema builder
	 */
	private OpenApiSchemaBuilder $schemaBuilder;

	/**
	 * Constructor
	 * @param OpenApiSchemaBuilder $schemaBuilder OpenAPI schema builder
	 * @param RestApiSchemaValidator $validator REST API JSON schema validator
	 */
	public function __construct(OpenApiSchemaBuilder $schemaBuilder, RestApiSchemaValidator $validator) {
		$this->schemaBuilder = $schemaBuilder;
		parent::__construct($validator);
	}

	/**
	 * @Path("/")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: Returns OpenAPI schema
	 *  security:
	 *     - []
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/OpenApiSpecification'
	 * ")
	 */
	public function index(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $response->writeJsonBody($this->schemaBuilder->getArray());
	}

}
