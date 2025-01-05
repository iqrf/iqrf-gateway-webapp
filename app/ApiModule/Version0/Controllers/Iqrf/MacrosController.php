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
use App\ApiModule\Version0\Controllers\IqrfController;
use App\ApiModule\Version0\Models\RestApiSchemaValidator;
use Iqrf\IdeMacros\MacroFileParser;

/**
 * IQRF IDE Macros controller
 * @Path("/macros")
 */
class MacrosController extends IqrfController {

	/**
	 * @var MacroFileParser IQRF IDE Macros parser
	 */
	private MacroFileParser $macroParser;

	/**
	 * Constructor
	 * @param MacroFileParser $macroParser IQRF IDE Macros parser
	 * @param RestApiSchemaValidator $validator REST API JSON schema validator
	 */
	public function __construct(MacroFileParser $macroParser, RestApiSchemaValidator $validator) {
		$this->macroParser = $macroParser;
		parent::__construct($validator);
	}

	/**
	 * @Path("/")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: Returns IQRF IDE macros
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/IqrfIdeMacros'
	 *      '403':
	 *          $ref: '#/components/responses/Forbidden'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function macros(ApiRequest $request, ApiResponse $response): ApiResponse {
		self::checkScopes($request, ['iqrf:macros']);
		return $response->writeJsonBody($this->macroParser->read());
	}

}
