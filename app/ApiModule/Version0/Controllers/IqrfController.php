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
use App\ConfigModule\Models\IqrfManager;
use Iqrf\IdeMacros\MacroFileParser;

/**
 * IQRF network manager
 * @Path("/iqrf")
 * @Tag("IQRF network")
 */
class IqrfController extends BaseController {

	/**
	 * @var IqrfManager IQRF interfaces manager
	 */
	private $interfacesManager;

	/**
	 * @var MacroFileParser IQRF IDE Macros parser
	 */
	private $macroParser;

	/**
	 * Constructor
	 * @param IqrfManager $interfacesManager IQRF interfaces manager
	 * @param MacroFileParser $macroParser IQRF IDE Macros parser
	 */
	public function __construct(IqrfManager $interfacesManager, MacroFileParser $macroParser) {
		$this->interfacesManager = $interfacesManager;
		$this->macroParser = $macroParser;
	}

	/**
	 * @Path("/interfaces")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: Returns IQRF interfaces
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/IqrfInterfaces'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function interfaces(ApiRequest $request, ApiResponse $response): ApiResponse {
		$interfaces = [
			'cdc' => $this->interfacesManager->getCdcInterfaces(),
			'spi' => $this->interfacesManager->getSpiInterfaces(),
			'uart' => $this->interfacesManager->getUartInterfaces(),
		];
		return $response->writeJsonBody($interfaces);
	}

	/**
	 * @Path("/macros")
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
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function macros(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $response->writeJsonBody($this->macroParser->read());
	}

}
