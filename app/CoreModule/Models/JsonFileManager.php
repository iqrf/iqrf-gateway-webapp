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

namespace App\CoreModule\Models;

use Nette\IOException;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use stdClass;

/**
 * Tool for reading and writing JSON files
 */
class JsonFileManager extends FileManager {

	/**
	 * Deletes the JSON file
	 * @param string $fileName File name
	 * @throws IOException
	 */
	public function delete(string $fileName): void {
		parent::delete($fileName . '.json');
	}

	/**
	 * Checks if the JSON file exists
	 * @param string $fileName File name
	 * @return bool Is file exists?
	 */
	public function exists(string $fileName): bool {
		return parent::exists($fileName . '.json');
	}

	/**
	 * Reads the JSON file and decode it to array
	 * @param string $fileName File name (without .json)
	 * @param bool $forceArray Force object to array conversion
	 * @return array<mixed>|stdClass JSON data in array
	 * @throws IOException
	 * @throws JsonException
	 */
	public function read(string $fileName, bool $forceArray = true) {
		$file = parent::read($fileName . '.json');
		$flags = $forceArray ? Json::FORCE_ARRAY : 0;
		return Json::decode($file, $flags);
	}

	/**
	 * Encodes the JSON from array and write into the JSON file
	 * @param string $name File name (without .json)
	 * @param mixed $array JSON data in array
	 * @throws IOException
	 * @throws JsonException
	 */
	public function write(string $name, $array): void {
		$json = Json::encode($array, Json::PRETTY);
		parent::write($name . '.json', $json);
	}

}
