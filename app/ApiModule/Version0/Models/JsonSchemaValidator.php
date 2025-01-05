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

namespace App\ApiModule\Version0\Models;

use Apitte\Core\Http\ApiRequest;
use App\CoreModule\Exceptions\InvalidJsonException;
use App\CoreModule\Exceptions\NonexistentJsonSchemaException;
use App\CoreModule\Models\CommandManager;
use App\CoreModule\Models\FileManager;
use JsonSchema\Validator;
use Nette\Utils\JsonException;
use stdClass;

/**
 * API JSON schema validator
 */
class JsonSchemaValidator extends FileManager {

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command manager
	 */
	public function __construct(CommandManager $commandManager) {
		$configDir = __DIR__ . '/../../../../api/schemas';
		parent::__construct($configDir, $commandManager);
	}

	/**
	 * Validates JSON
	 * @param string $schema JSON schema file name
	 * @param ApiRequest $request API request
	 * @throws InvalidJsonException
	 * @throws JsonException
	 */
	public function validate(string $schema, ApiRequest $request): void {
		$fileName = $schema . '.json';
		if (!parent::exists($fileName)) {
			$message = 'Non-existing JSON schema ' . $schema . '.';
			throw new NonexistentJsonSchemaException($message);
		}
		$json = $request->getJsonBodyCopy(false);
		if (!is_array($json) && !($json instanceof stdClass)) {
			$message = 'Invalid JSON format';
			throw new InvalidJsonException($message);
		}
		$validator = new Validator();
		$validator->validate($json, parent::readJson($fileName));
		if (!$validator->isValid()) {
			$message = 'JSON does not validate. JSON schema: ' . $schema . ' Violations:';
			foreach ($validator->getErrors() as $error) {
				$message .= PHP_EOL . '[' . $error['property'] . '] ' . $error['message'];
			}
			throw new InvalidJsonException($message);
		}
	}

}
