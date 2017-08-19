<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
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

namespace App\ConfigModule\Model;

use App\Model\JsonFileManager;
use Nette;
use Nette\Utils\ArrayHash;

class GenericManager {

	use Nette\SmartObject;

	/**
	 * @var JsonFileManager
	 */
	private $fileManager;

	/**
	 * Constructor
	 * @param JsonFileManager $fileManager
	 */
	public function __construct(JsonFileManager $fileManager) {
		$this->fileManager = $fileManager;
	}

	/**
	 * Load configuration
	 * @param string $fileName File name (without .json)
	 * @return array Array for form
	 */
	public function load($fileName) {
		return $this->fileManager->read($fileName);
	}

	/**
	 * Save configuration
	 * @param string $fileName File name (without .json)
	 * @param ArrayHash $array Settings
	 */
	public function save($fileName, ArrayHash $array) {
		$this->fileManager->write($fileName, $array);
	}

}
