<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2018 IQRF Tech s.r.o.
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

namespace App\ConfigModule\Model;

use App\Model\JsonFileManager;
use Nette;
use Nette\Utils\ArrayHash;
use Nette\Utils\Finder;
use Nette\Utils\Strings;

class GenericManager {

	use Nette\SmartObject;

	/**
	 * @var JsonFileManager JSON file manager
	 */
	private $fileManager;

	/**
	 *
	 * @var string File name (without .json)
	 */
	private $fileName;

	/**
	 * Constructor
	 * @param JsonFileManager $fileManager JSON file manager
	 */
	public function __construct(JsonFileManager $fileManager) {
		$this->fileManager = $fileManager;
	}

	/**
	 * Load configuration
	 * @return array Array for form
	 */
	public function load(): array {
		return $this->fileManager->read($this->fileName);
	}

	/**
	 * Save configuration
	 * @param ArrayHash $array Settings
	 */
	public function save(ArrayHash $array) {
		$this->fileManager->write($this->fileName, $array);
	}

	/**
	 * Set file name
	 * @param string $fileName File name (without .json)
	 */
	public function setFileName(string $fileName) {
		$this->fileName = $fileName;
	}

	/**
	 * Get component's instamces
	 * @param string $component Component name
	 * @return array Files with instances of components
	 */
	public function getInstances(string $component): array {
		$dir = $this->fileManager->getDirectory();
		$instances = [];
		foreach (Finder::findFiles('*.json')->exclude('config.json')->from($dir) as $file) {
			$fileName = Strings::replace($file->getFilename(), '/.json$/', '');
			$json = $this->fileManager->read($fileName);
			if (array_key_exists('component', $json) && $json['component'] === $component) {
				$instances[] = $fileName;
			}
		}
		return $instances;
	}

}
