<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2018 IQRF Tech s.r.o.
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

namespace App\CoreModule\Models;

use App\CoreModule\Exceptions\InvalidJsonException;
use App\CoreModule\Exceptions\NonExistingJsonSchemaException;
use JsonSchema\Validator;
use Nette\SmartObject;
use Nette\Utils\JsonException;
use Nette\Utils\Strings;

/**
 * Tool for reading and validating JSON schemas
 */
class JsonSchemaManager extends JsonFileManager {

	use SmartObject;

	/**
	 * @var string JSON schema file name
	 */
	private $schema;

	/**
	 * Constructor
	 * @param string $configDir Directory with JSON schemas
	 */
	public function __construct(string $configDir) {
		parent::__construct($configDir);
	}

	/**
	 * Set file name of JSON schema from the component name
	 * @param string $component Component name
	 * @throws NonExistingJsonSchemaException
	 */
	public function setSchemaFromComponent(string $component): void {
		$schema = 'schema__' . Strings::replace($component, '~::~', '__');
		if (parent::exists($schema)) {
			$this->schema = $schema;
		} else {
			$message = 'Non-existing JSON schema ' . $schema . '.';
			throw new NonExistingJsonSchemaException($message);
		}
	}

	/**
	 * Validate JSON
	 * @param \stdClass $json JSON to validate
	 * @return boolean Is the JSON valid?
	 * @throws InvalidJsonException
	 * @throws JsonException
	 */
	public function validate(\stdClass $json): bool {
		$schema = parent::read($this->schema);
		$validator = new Validator();
		$validator->validate($json, $schema);
		if (!$validator->isValid()) {
			$message = 'JSON does not validate. JSON schema: ' . $this->schema . ' Violations:';
			foreach ($validator->getErrors() as $error) {
				$message .= PHP_EOL . '[' . $error['property'] . '] ' . $error['message'];
			}
			throw new InvalidJsonException($message);
		}
		return true;
	}

}
