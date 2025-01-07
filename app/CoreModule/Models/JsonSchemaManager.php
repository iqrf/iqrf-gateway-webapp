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

namespace App\CoreModule\Models;

use App\CoreModule\Exceptions\InvalidJsonException;
use App\CoreModule\Exceptions\NonexistentJsonSchemaException;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Constraints\Factory;
use JsonSchema\SchemaStorage;
use JsonSchema\Validator;
use Nette\Utils\JsonException;
use stdClass;

/**
 * Tool for reading and validating JSON schemas
 */
class JsonSchemaManager extends FileManager {

	/**
	 * @var string JSON schema file name
	 */
	private string $schema;

	/**
	 * @var SchemaStorage|null JSON schema storage
	 */
	private ?SchemaStorage $storage = null;

	/**
	 * Sets the JSON schema file name
	 * @param string $schema JSON schema file name
	 * @throws NonexistentJsonSchemaException
	 */
	public function setSchema(string $schema): void {
		if (!parent::exists($schema . '.json')) {
			$message = 'Non-existing JSON schema ' . $schema . '.';
			throw new NonexistentJsonSchemaException($message);
		}
		$this->schema = $schema;
	}

	/**
	 * Returns the JSON schema storage
	 * @return SchemaStorage|null JSON schema storage
	 */
	public function getStorage(): ?SchemaStorage {
		return $this->storage;
	}

	/**
	 * Sets the JSON schema storage
	 * @param SchemaStorage|null $storage JSON schema storage
	 */
	public function setStorage(?SchemaStorage $storage): void {
		$this->storage = $storage;
	}

	/**
	 * Validates JSON
	 * @param mixed $json JSON to validate
	 * @param bool $tryFix Try to fix JSON?
	 * @throws InvalidJsonException
	 * @throws JsonException
	 */
	public function validate(mixed $json, bool $tryFix = false): void {
		if (!is_array($json) && !($json instanceof stdClass)) {
			$message = 'Invalid JSON format';
			throw new InvalidJsonException($message);
		}
		$schema = parent::readJson($this->schema . '.json');
		$validator = new Validator(new Factory($this->storage));
		$checkMode = null;
		if ($tryFix) {
			$checkMode = Constraint::CHECK_MODE_TYPE_CAST | Constraint::CHECK_MODE_COERCE_TYPES | Constraint::CHECK_MODE_APPLY_DEFAULTS | Constraint::CHECK_MODE_ONLY_REQUIRED_DEFAULTS;
		}
		$validator->validate($json, $schema, $checkMode);
		if (!$validator->isValid()) {
			$message = 'JSON does not validate. JSON schema: ' . $this->schema . ' Violations:';
			foreach ($validator->getErrors() as $error) {
				$message .= PHP_EOL . '[' . $error['property'] . '] ' . $error['message'];
			}
			throw new InvalidJsonException($message);
		}
	}

}
