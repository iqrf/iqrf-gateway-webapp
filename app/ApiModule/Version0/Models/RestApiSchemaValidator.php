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

namespace App\ApiModule\Version0\Models;

use Apitte\Core\Http\ApiRequest;
use App\CoreModule\Exceptions\InvalidJsonException;
use App\CoreModule\Models\JsonSchemaManager;
use Nette\Utils\JsonException;

/**
 * REST API JSON schema validator
 */
class RestApiSchemaValidator extends JsonSchemaManager {

	/**
	 * Validates REST API request
	 * @param string $schema REST API JSON schema name
	 * @param ApiRequest $request REST API request
	 * @throws JsonException
	 * @throws InvalidJsonException
	 */
	public function validateRequest(string $schema, ApiRequest $request): void {
		$this->setSchema($schema);
		$this->validate($request->getJsonBody(false));
	}

}
