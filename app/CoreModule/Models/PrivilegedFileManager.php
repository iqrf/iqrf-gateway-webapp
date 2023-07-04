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
use Nette\Utils\Strings;

/**
 * Privileged file manager
 */
class PrivilegedFileManager implements IFileManager {

	/**
	 * @var CommandManager Command manager
	 */
	private CommandManager $commandManager;

	/**
	 * @var string Directory path
	 */
	private string $directory;

	/**
	 * Constructor
	 * @param string $directory Directory path
	 * @param CommandManager $commandManager Command manager
	 */
	public function __construct(string $directory, CommandManager $commandManager) {
		$this->commandManager = $commandManager;
		$this->directory = rtrim(Strings::replace($directory, '~\'~', '\\\''), '/');
	}

	/**
	 * Changes the owner of a file or directory
	 * @param string|null $path Path to the file or directory
	 * @param string $user User name
	 * @param string|null $group Group name
	 * @param bool $recursive Indicates whether the owner should be changed recursively
	 */
	public function chown(?string $path, string $user, ?string $group = null, bool $recursive = false): void {
		$owner = escapeshellarg($user . ($group !== null ? ':' . $group : ''));
		$args = $recursive ? '-R ' : '';
		$command = $this->commandManager->run('chown ' . $args . $owner . ' ' . $this->buildPath($path), true);
		if ($command->getExitCode() !== 0) {
			throw new IOException($command->getStderr());
		}
	}

	/**
	 * Changes the permissions of a file or directory
	 * @param string|null $path Path to the file or directory
	 * @param int $mode File mode
	 * @param bool $recursive Indicates whether the mode should be changed recursively
	 */
	public function chmod(?string $path, int $mode, bool $recursive = false): void {
		$args = $recursive ? '-R ' : '';
		$command = $this->commandManager->run('chmod ' . $args . decoct($mode) . ' ' . $this->buildPath($path), true);
		if ($command->getExitCode() !== 0) {
			throw new IOException($command->getStderr());
		}
	}

	/**
	 * Reads the file
	 * @param string $fileName File name
	 * @return string File content
	 * @throws IOException
	 */
	public function read(string $fileName): string {
		$command = $this->commandManager->run('cat ' . $this->buildPath($fileName), true);
		if ($command->getExitCode() !== 0) {
			throw new IOException($command->getStderr());
		}
		return $command->getStdout();
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
	 * Deletes the file
	 * @param string $fileName File name
	 * @throws IOException
	 */
	public function delete(string $fileName): void {
		$command = $this->commandManager->run('rm -rf ' . $this->buildPath($fileName), true);
		if ($command->getExitCode() !== 0) {
			throw new IOException($command->getStderr());
		}
	}
	/**
	 * Checks if the file exists
	 * @param string $fileName File name
	 * @return bool Is file exists?
	 */
	public function exists(string $fileName): bool {
		$command = $this->commandManager->run('test -e ' . $this->buildPath($fileName), true);
		return $command->getExitCode() === 0;
	}

	/**
	 * Writes into the file
	 * @param string $fileName File name
	 * @param mixed $content File content
	 * @throws IOException
	 */
	public function write(string $fileName, mixed $content): void {
		$dirName = dirname($fileName);
		if ($dirName === '.') {
			$dirName = '';
		}
		$command = $this->commandManager->run('mkdir -p ' . $this->buildPath($dirName), true);
		if ($command->getExitCode() !== 0) {
			throw new IOException($command->getStderr());
		}
		$command = $this->commandManager->run('tee ' . $this->buildPath($fileName), true, 60, $content);
		if ($command->getExitCode() !== 0) {
			throw new IOException($command->getStderr());
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
	 * Copies file to a destination
	 * @param string $destination Destination path
	 * @param string $fileName Source file path
	 */
	public function copy(string $destination, string $fileName): void {
		$this->write($destination, FileSystem::read($fileName));
	}

	/**
	 * Returns list of subdirectories in directory
	 * @param string|null $subdirectory Relative path to subdirectory
	 * @return array<int, string> List of directories
	 */
	public function listDirectories(?string $subdirectory = null): array {
		$command = $this->commandManager->run('find ' . $this->buildPath($subdirectory) . ' -mindepth 1 -type d -printf \'%P\n\'', true);
		if ($command->getExitCode() !== 0) {
			throw new IOException($command->getStderr());
		}
		$output = $command->getStdout();
		if ($output === '') {
			return [];
		}
		return explode(PHP_EOL, $output);
	}

	/**
	 * Returns list of files in directory
	 * @param string|null $subdirectory Relative path to subdirectory
	 * @return array<int, string> List of files
	 */
	public function listFiles(?string $subdirectory = null): array {
		$command = $this->commandManager->run('find ' . $this->buildPath($subdirectory) . ' -type f -printf \'%P\n\'', true);
		if ($command->getExitCode() !== 0) {
			throw new IOException($command->getStderr());
		}
		$output = $command->getStdout();
		if ($output === '') {
			return [];
		}
		return explode(PHP_EOL, $output);
	}

	/**
	 * Returns Base directory path
	 * @return string Base directory path
	 */
	public function getBasePath(): string {
		return $this->directory;
	}

	/**
	 * Returns path to subdirectory or file, if the path contains spaces, returned string is surrounded in quotation marks
	 * @param string|null $name Name of subdirectory or file
	 * @return string Path to subdirectory or file
	 */
	private function buildPath(?string $name): string {
		return escapeshellarg($this->directory . ($name !== null ? '/' . $name : ''));
	}

}
