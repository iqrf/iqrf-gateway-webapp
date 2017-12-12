<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017 IQRF Tech s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace App\Model;

use Nette;
use Nette\Utils\Json;

/**
 * Tool for reading and writing JSON files.
 */
class JsonFileManager extends FileManager {

	use Nette\SmartObject;

	/**
	 * Constructor
	 * @param string $configDir Directory with configuration files
	 */
	public function __construct(string $configDir) {
		parent::__construct($configDir);
	}

	/**
	 * Read JSON file and decode JSON to array
	 * @param string $fileName File name (without .json)
	 * @return array
	 */
	public function read(string $fileName) {
		$file = parent::read($fileName . '.json');
		return Json::decode($file, Json::FORCE_ARRAY);
	}

	/**
	 * Encode JSON from array and write JSON file
	 * @param string $name File name (without .json)
	 * @param array $array JSON array
	 */
	public function write(string $name, $array) {
		$json = Json::encode($array, Json::PRETTY);
		parent::write($name . '.json', $json);
	}

}
