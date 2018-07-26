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

use Nette;
use Nette\IOException;
use Nette\Utils\FileSystem;
use Tracy\Debugger;

/**
 * Tool for reading and writing text files.
 */
class FileManager {

	use Nette\SmartObject;

	/**
	 * @var string
	 */
	private $configDir;

	/**
	 * Constructor
	 * @param string $configDir Directory with files
	 */
	public function __construct(string $configDir) {
		$this->configDir = $configDir;
	}

	/**
	 * Return directory with files
	 * @return string Directory with files
	 */
	public function getDirectory(): string {
		return $this->configDir;
	}

	/**
	 * Delete a file
	 * @param string $fileName File name
	 */
	public function delete(string $fileName) {
		try {
			FileSystem::delete($this->configDir . '/' . $fileName);
		} catch (IOException $e) {
			Debugger::log($e->getMessage(), 'fileManager');
			throw $e;
		}
	}

	/**
	 * Check if file exists
	 * @param string $fileName File name
	 * @return bool Is file exists?
	 */
	public function exists(string $fileName): bool {
		return file_exists($this->configDir . '/' . $fileName);
	}

	/**
	 * Read file
	 * @param string $fileName File name
	 * @return string File content
	 */
	public function read(string $fileName) {
		try {
			return FileSystem::read($this->configDir . '/' . $fileName);
		} catch (IOException $e) {
			Debugger::log($e->getMessage(), 'fileManager');
			throw $e;
		}
	}

	/**
	 * Encode JSON from array and write JSON file
	 * @param string $fileName File name
	 * @param string $content File content
	 */
	public function write(string $fileName, $content) {
		$fileName = 'nette.safe://' . $this->configDir . '/' . $fileName;
		try {
			FileSystem::write($fileName, $content, null);
		} catch (IOException $e) {
			Debugger::log($e->getMessage(), 'fileManager');
			throw $e;
		}
	}

}
