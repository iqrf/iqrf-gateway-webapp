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

namespace App\IqrfNetModule\Models;

use App\CoreModule\Exceptions\NonexistentJsonSchemaException;
use App\CoreModule\Models\JsonSchemaManager;

/**
 * IQRF Gateway Daemon API message JSON schema manager
 */
class ApiSchemaManager extends JsonSchemaManager {

	/**
	 * Sets the file name of JSON schema from the API message type
	 * @param string $mType Message type
	 * @throws NonexistentJsonSchemaException
	 */
	public function setSchemaForRequest(string $mType): void {
		$fileName = $mType . '-request-1-0-0';
		parent::setSchema($fileName);
	}

	/**
	 * Sets the file name of JSON schema from the API message type
	 * @param string $mType Message type
	 * @throws NonexistentJsonSchemaException
	 */
	public function setSchemaForResponse(string $mType): void {
		$fileName = $mType . '-response-1-0-0';
		parent::setSchema($fileName);
	}

}
