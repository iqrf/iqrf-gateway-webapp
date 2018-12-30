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

use Nette\SmartObject;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

/**
 * Tool for reading and writing JSON files
 */
class JsonFileManager extends FileManager {

	use SmartObject;

	/**
	 * Constructor
	 * @param string $configDir Directory with configuration files
	 */
	public function __construct(string $configDir) {
		parent::__construct($configDir);
	}

	/**
	 * Delete a JSON file
	 * @param string $fileName File name
	 */
	public function delete(string $fileName): void {
		parent::delete($fileName . '.json');
	}

	/**
	 * Check if file exists
	 * @param string $fileName File name
	 * @return bool Is file exists?
	 */
	public function exists(string $fileName): bool {
		return parent::exists($fileName . '.json');
	}

	/**
	 * Read JSON file and decode JSON to array
	 * @param string $fileName File name (without .json)
	 * @return mixed[] JSON data in array
	 * @throws JsonException
	 */
	public function read(string $fileName): array {
		$file = parent::read($fileName . '.json');
		return Json::decode($file, Json::FORCE_ARRAY);
	}

	/**
	 * Encode JSON from array and write the JSON file
	 * @param string $name File name (without .json)
	 * @param mixed $array JSON data in array
	 * @throws JsonException
	 */
	public function write(string $name, $array): void {
		$json = Json::encode($array, Json::PRETTY);
		parent::write($name . '.json', $json);
	}

}
