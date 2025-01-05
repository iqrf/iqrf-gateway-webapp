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

use Nette\IOException;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Nette\Utils\Strings;

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
	 * Returns Base directory path
	 * @return string Base directory path
	 */
	public function getBasePath(): string {
		return $this->directory;
	}

	/**
	 * Creates the symbolic link
	 * @param string $target Target of the symbolic link
	 * @param string $link Symbolic link name
	 * @throws IOException Failed to create symbolic link
	 */
	public function createSymLink(string $target, string $link): void {
		$this->delete($link);
		$link = $this->directory . '/' . $link;
		$target = $this->directory . '/' . $target;
		@mkdir(dirname($link), 0755, true);
		$result = symlink($target, $link);
		if (!$result) {
			throw new IOException('Failed to create symbolic link: ' . $link);
		}
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
	 * Changes the permissions of a file or directory
	 * @param string|null $path Path to the file or directory
	 * @param int $mode File mode
	 * @param bool $recursive Indicates whether the mode should be changed recursively
	 */
	public function chmod(?string $path, int $mode, bool $recursive = false): void {
		if (!$recursive) {
			chmod($this->directory . '/' . $path, $mode);
			return;
		}
		foreach (Finder::find('*')->from($this->getRealPath(dirname($path))) as $file) {
			chmod($file->getRealPath(), $mode);
		}
	}

	/**
	 * Checks if the file is symbolic link
	 * @param string $fileName File name
	 * @return bool Is symbolic link?
	 */
	public function isSymLink(string $fileName): bool {
		$path = $this->directory . '/' . $fileName;
		if (!is_readable($this->directory) || !is_readable($path)) {
			$this->fixPermissions($fileName);
		}
		return is_link($path);
	}

	/**
	 * Returns list of subdirectories in directory
	 * @param string|null $subdirectory Relative path to subdirectory
	 * @return array<int, string> List of directories
	 */
	public function listDirectories(?string $subdirectory = null): array {
		$realPath = $this->getRealPath($subdirectory);
		$realPathLen = strlen($realPath) + 1;
		$files = iterator_to_array(Finder::findDirectories('*')->from($realPath)->getIterator());
		$files = array_map(static fn ($file): string => Strings::substring($file->getRealPath(), $realPathLen), $files);
		sort($files);
		return $files;
	}

	/**
	 * Returns list of files in directory
	 * @param string|null $subdirectory Relative path to subdirectory
	 * @return array<int, string> List of files
	 */
	public function listFiles(?string $subdirectory = null): array {
		$realPath = $this->getRealPath($subdirectory);
		$realPathLen = strlen($realPath) + 1;
		$files = iterator_to_array(Finder::findFiles('*')->from($realPath)->getIterator());
		$files = array_map(static fn ($file): string => Strings::substring($file->getRealPath(), $realPathLen), $files);
		sort($files);
		return $files;
	}

	/**
	 * Reads the file
	 * @param string $fileName File name
	 * @return mixed File content
	 * @throws IOException
	 */
	public function read(string $fileName) {
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
	public function readJson(string $fileName, bool $forceArray = true) {
		$file = $this->read($fileName);
		$flags = $forceArray ? Json::FORCE_ARRAY : 0;
		return Json::decode($file, $flags);
	}

	/**
	 * Writes into the file
	 * @param string $fileName File name
	 * @param mixed $content File content
	 * @throws IOException
	 */
	public function write(string $fileName, $content): void {
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
	public function writeJson(string $fileName, $content): void {
		$json = Json::encode($content, Json::PRETTY);
		$this->write($fileName, $json);
	}

	/**
	 * Returns the real path
	 * @param string|null $subdirectory Relative path to subdirectory
	 * @return string Real path
	 * @throws IOException Directory not found
	 */
	private function getRealPath(?string $subdirectory = null): string {
		$realPath = realpath($this->directory . '/' . ($subdirectory ?? ''));
		if ($realPath === false) {
			throw new IOException('Directory not found: ' . $this->directory . '/' . ($subdirectory ?? ''));
		}
		return $realPath;
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
