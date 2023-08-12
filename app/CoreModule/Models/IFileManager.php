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
use Nette\Utils\JsonException;

/**
 * File manager interface
 */
interface IFileManager {

	/**
	 * Returns Base directory path
	 * @return string Base directory path
	 */
	public function getBasePath(): string;

	/**
	 * Creates the symbolic link
	 * @param string $target Target of the symbolic link
	 * @param string $link Symbolic link name
	 */
	public function createSymLink(string $target, string $link): void;

	/**
	 * Deletes the file
	 * @param string $fileName File name
	 * @throws IOException
	 */
	public function delete(string $fileName): void;

	/**
	 * Checks if the file exists
	 * @param string $fileName File name
	 * @return bool Is file exists?
	 */
	public function exists(string $fileName): bool;

	/**
	 * Changes the permissions of a file or directory
	 * @param string|null $path Path to the file or directory
	 * @param int $mode File mode
	 * @param bool $recursive Indicates whether the mode should be changed recursively
	 */
	public function chmod(?string $path, int $mode, bool $recursive = false): void;

	/**
	 * Checks if the file is symbolic link
	 * @param string $fileName File name
	 * @return bool Is symbolic link?
	 */
	public function isSymLink(string $fileName): bool;

	/**
	 * Returns list of subdirectories in directory
	 * @param string|null $subdirectory Relative path to subdirectory
	 * @return array<int, string> List of directories
	 */
	public function listDirectories(?string $subdirectory = null): array;

	/**
	 * Returns list of files in directory
	 * @param string|null $subdirectory Relative path to subdirectory
	 * @return array<int, string> List of files
	 */
	public function listFiles(?string $subdirectory = null): array;

	/**
	 * Reads the file
	 * @param string $fileName File name
	 * @return string File content
	 * @throws IOException
	 */
	public function read(string $fileName): string;

	/**
	 * Reads the JSON file and decode it to array
	 * @param string $fileName File name
	 * @param bool $forceArray Force object to array conversion
	 * @return mixed Decoded JSON data
	 * @throws IOException
	 * @throws JsonException
	 */
	public function readJson(string $fileName, bool $forceArray = true): mixed;

	/**
	 * Writes into the file
	 * @param string $fileName File name
	 * @param mixed $content File content
	 * @throws IOException
	 */
	public function write(string $fileName, mixed $content): void;

	/**
	 * Encodes the JSON from array and write into the JSON file
	 * @param string $fileName File name
	 * @param mixed $content JSON data to encode
	 * @throws IOException
	 * @throws JsonException
	 */
	public function writeJson(string $fileName, mixed $content): void;

}
