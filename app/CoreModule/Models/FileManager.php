<?php

/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
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
use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

/**
 * Tool for reading and writing text files
 */
class FileManager implements IFileManager {

	/**
	 * @var string Directory with files
	 */
	private string $directory;

	/**
	 * @var CommandManager Command manager
	 */
	private CommandManager $commandManager;

	/**
	 * Constructor
	 * @param string $directory Directory with files
	 * @param CommandManager $commandManager Command managers
	 */
	public function __construct(string $directory, CommandManager $commandManager) {
		$this->directory = $directory;
		$this->commandManager = $commandManager;
	}

	/**
	 * Returns the directory with files
	 * @return string Directory with files
	 */
	public function getDirectory(): string {
		return $this->directory;
	}

	/**
	 * Deletes the file
	 * @param string $fileName File name
	 * @throws IOException
	 */
	public function delete(string $fileName): void {
		try {
			FileSystem::delete($this->directory . '/' . $fileName);
		} catch (IOException $e) {
			$this->fixPermissions($fileName);
			FileSystem::delete($this->directory . '/' . $fileName);
		}
	}

	/**
	 * Checks if the file exists
	 * @param string $fileName File name
	 * @return bool Is file exists?
	 */
	public function exists(string $fileName): bool {
		$path = $this->directory . '/' . $fileName;
		if (!is_readable($this->directory) || !is_readable($path)) {
			$this->fixPermissions($fileName);
		}
		return file_exists($path);
	}

	/**
	 * Reads the file
	 * @param string $fileName File name
	 * @return string File content
	 * @throws IOException
	 */
	public function read(string $fileName): string {
		try {
			return FileSystem::read($this->directory . '/' . $fileName);
		} catch (IOException $e) {
			$this->fixPermissions($fileName);
			return FileSystem::read($this->directory . '/' . $fileName);
		}
	}

	/**
	 * Reads the JSON file and decode it to array
	 * @param string $fileName File name
	 * @param bool $forceArray Force object to array conversion
	 * @return mixed Decoded JSON data
	 * @throws IOException
	 * @throws JsonException
	 */
	public function readJson(string $fileName, bool $forceArray = true): mixed {
		$file = $this->read($fileName);
		return Json::decode($file, forceArrays: $forceArray);
	}

	/**
	 * Writes into the file
	 * @param string $fileName File name
	 * @param mixed $content File content
	 * @throws IOException
	 */
	public function write(string $fileName, mixed $content): void {
		$path = 'nette.safe://' . $this->directory . '/' . $fileName;
		try {
			FileSystem::write($path, $content, null);
		} catch (IOException $e) {
			$this->fixPermissions($fileName);
			FileSystem::write($path, $content, null);
		}
	}

	/**
	 * Encodes the JSON from array and write into the JSON file
	 * @param string $fileName File name
	 * @param mixed $content JSON data to encode
	 * @throws IOException
	 * @throws JsonException
	 */
	public function writeJson(string $fileName, mixed $content): void {
		$json = Json::encode($content, pretty: true);
		$this->write($fileName, $json);
	}

	/**
	 * Returns Base directory path
	 * @return string Base directory path
	 */
	public function getBasePath(): string {
		return $this->directory;
	}

	/**
	 * Fixes the permissions
	 * @param string $fileName File name
	 */
	private function fixPermissions(string $fileName): void {
		$this->commandManager->run('chmod 777 ' . escapeshellarg($this->directory), true);
		$this->commandManager->run('chmod 666 ' . escapeshellarg($this->directory . '/' . $fileName), true);
	}

}
