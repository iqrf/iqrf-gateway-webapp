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

namespace App\ConfigModule\Models;

use App\CoreModule\Exceptions\NonexistentJsonSchemaException;
use App\CoreModule\Models\JsonSchemaManager;
use Nette\Utils\Strings;

/**
 * Component JSON schema manager
 */
class ComponentSchemaManager extends JsonSchemaManager {

	/**
	 * Sets the file name of JSON schema from the component name
	 * @param string $component Component name
	 * @throws NonexistentJsonSchemaException
	 */
	public function setSchema(string $component): void {
		$fileName = 'schema__' . Strings::replace($component, '#::#', '__');
		parent::setSchema($fileName);
	}

}
