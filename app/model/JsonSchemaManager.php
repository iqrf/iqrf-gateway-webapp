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

namespace App\Model;

use App\Model\InvalidJson;
use App\Model\NonExistingJsonSchema;
use JsonSchema\Validator;
use Nette;
use Nette\Utils\Strings;
use Tracy\Debugger;

/**
 * Tool for reading and validationg JSON schemas.
 */
class JsonSchemaManager extends JsonFileManager {

	use Nette\SmartObject;

	/**
	 * @var string JSON Schema file name
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
	 * Set file name of JSON schema from component name
	 * @param string $component Component name
	 */
	public function setSchemaFromComponent(string $component) {
		$schema = 'schema__' . Strings::replace($component, '~::~', '__');
		if (parent::exists($schema)) {
			$this->schema = $schema;
		} else {
			$errorMsg = 'Non-existing JSON schema ' . $schema . '.';
			Debugger::log($errorMsg, 'jsonSchema');
			throw new NonExistingJsonSchema();
		}
	}

	/**
	 * Validate JSON
	 * @param \stdClass $json JSON to validate
	 */
	public function validate(\stdClass $json) {
		$schema = parent::read($this->schema);
//		$schema['type'] = 'array';
//		if (array_key_exists('RequiredInterfaces', $schema['properties'])) {
//			$schema['properties']['RequiredInterfaces']['items']['type'] = 'array';
//			$schema['properties']['RequiredInterfaces']['items']['properties']['target']['type'] = 'array';
//		}
//		if (array_key_exists('VerbosityLevels', $schema['properties'])) {
//			$schema['properties']['VerbosityLevels']['items']['type'] = 'array';
//		}
		$validator = new Validator();
		$validator->validate($json, $schema);
		if (!$validator->isValid()) {
			$errorMsg = 'JSON does not validate. JSON schema: ' . $this->schema . ' Violations:';
			foreach ($validator->getErrors() as $error) {
				$errorMsg .= PHP_EOL . '[' . $error['property'] . '] ' . $error['message'];
			}
			Debugger::log($errorMsg, 'jsonSchema');
			throw new InvalidJson();
		}
		return true;
	}

}
