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
use App\ApiModule\Version0\Models\ControllerValidators;
use Iqrf\IdeMacros\MacroFileParser;

/**
 * IQRF IDE Macros controller
 */
#[Path('/macros')]
class MacrosController extends BaseIqrfController {

	/**
	 * Constructor
	 * @param MacroFileParser $macroParser IQRF IDE Macros parser
	 * @param ControllerValidators $validators Controller validators
	 */
	public function __construct(
		private readonly MacroFileParser $macroParser,
		ControllerValidators $validators,
	) {
		parent::__construct($validators);
	}

	#[Path('/')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Returns IQRF IDE macros
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/IqrfIdeMacros'
			'403':
				$ref: '#/components/responses/Forbidden'
	EOT)]
	public function macros(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['iqrf:macros']);
		$response = $response->writeJsonBody($this->macroParser->read());
		return $this->validators->validateResponse('iqrfIdeMacros', $response);
	}

}
