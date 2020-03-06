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
use Iqrf\IdeMacros\MacroFileParser;

/**
 * IQRF network manager
 * @Path("/iqrf")
 * @Tag("IQRF network")
 */
class IqrfController extends BaseController {

	/**
	 * @var MacroFileParser IQRF IDE Macros parser
	 */
	private $macroParser;

	/**
	 * Constructor
	 * @param MacroFileParser $macroParser IQRF IDE Macros parser
	 */
	public function __construct(MacroFileParser $macroParser) {
		$this->macroParser = $macroParser;
	}

	/**
	 * @Path("/macros")
	 * @Method("GET")
	 * @OpenApi("
	 *   summary: Returns IQRF IDE macros
	 * ")
	 * @Responses({
	 *      @Response(code="200", description="Success", entity="\App\ApiModule\Version0\Entities\Response\MacroGroupEntity[]")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function macros(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $response->writeJsonBody($this->macroParser->read());
	}

}
