<?php

/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
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

use Nette\Utils\Finder;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use UnexpectedValueException;
use ZipArchive;

/**
 * Tool for creating a new zip archive
 */
class ZipArchiveManager {

	/**
	 * @var ZipArchive ZIP archive
	 */
	private readonly ZipArchive $zip;

	/**
	 * Constructor
	 * @param string $path Path of a new zip archive
	 * @param int $flags The mode to use to open the archive
	 */
	public function __construct(string $path, int $flags = ZipArchive::CREATE | ZipArchive::OVERWRITE) {
		$this->zip = new ZipArchive();
		$this->zip->open($path, $flags);
	}

	/**
	 * Adds a file to the ZIP archive using its contents
	 * @param string $filename File name
	 * @param string $content The content of text file
	 */
	public function addFileFromText(string $filename, string $content): void {
		$dirname = dirname($filename);
		if ($dirname !== '.') {
			$this->addEmptyFolder($dirname);
		}
		$this->zip->addFromString($filename, $content);
	}

	/**
	 * Adds an empty folder to the ZIP archive from the given path
	 * @param string $directory Directory path
	 */
	public function addEmptyFolder(string $directory): void {
		$tokens = explode(DIRECTORY_SEPARATOR, $directory);
		$dirs = [];
		foreach ($tokens as $token) {
			$dirs[] = $token;
			$this->zip->addEmptyDir(implode('/', $dirs));
		}
	}

	/**
	 * Adds a folder to the ZIP archive from the given path
	 * @param string $path The path to the folder to add
	 * @param string $name Directory name in the archive
	 */
	public function addFolder(string $path, string $name): void {
		$this->zip->addEmptyDir($name);
		if ($name !== '' && !str_ends_with($name, '/')) {
			$name .= '/';
		}
		try {
			$files = Finder::findFiles('*')->in($path);
			foreach ($files as $file) {
				if ($file->isReadable()) {
					$this->addFile($file->getRealPath(), $name . $file->getFilename());
				}
			}
		} catch (UnexpectedValueException) {
			// Does nothing - an empty folder
		}
		try {
			$directories = Finder::findDirectories('*')->in($path);
			foreach ($directories as $directory) {
				if ($directory->isReadable()) {
					$this->addFolder($directory->getRealPath(), $name . $directory->getBasename());
				}
			}
		} catch (UnexpectedValueException) {
			// Does nothing - an empty directory
		}
	}

	/**
	 * Adds a file to a ZIP archive from the given path
	 * @param string $path The path to the file to add
	 * @param string $filename File name in the archive
	 */
	public function addFile(string $path, string $filename): void {
		$dirname = dirname($filename);
		if ($dirname !== '.') {
			$this->addEmptyFolder($dirname);
		}
		$this->zip->addFile($path, $filename);
	}

	/**
	 * Adds a JSON file to the ZIP archive using its contents
	 * @param string $filename File name
	 * @param array<mixed> $jsonData JSON data in an array
	 * @throws JsonException
	 */
	public function addJsonFromArray(string $filename, array $jsonData): void {
		$json = Json::encode($jsonData, pretty: true);
		$dirname = dirname($filename);
		if ($dirname !== '.') {
			$this->addEmptyFolder($dirname);
		}
		$this->zip->addFromString($filename, $json);
	}

	/**
	 * Deletes the directory
	 * @param string $name Name of the directory to delete
	 */
	public function deleteDirectory(string $name): void {
		if (!str_ends_with($name, '/')) {
			$name .= '/';
		}
		foreach ($this->listFiles() as $file) {
			if (str_starts_with($file, $name)) {
				$this->deleteFile($file);
			}
		}
		$this->zip->deleteName($name);
	}

	/**
	 * Deletes the file
	 * @param string $name Name of the file to delete
	 */
	public function deleteFile(string $name): void {
		$this->zip->deleteName($name);
	}

	/**
	 * Checks if the file or the files exist in the archive
	 * @param string|array<mixed>|iterable|mixed $var File(s) to check
	 * @return bool Is file exist
	 */
	public function exist(mixed $var): bool {
		if (is_string($var)) {
			return $this->zip->locateName($var, ZipArchive::FL_NOCASE) !== false;
		}
		if (is_iterable($var)) {
			foreach ($var as $file) {
				$result = $this->zip->locateName($file, ZipArchive::FL_NOCASE);
				if (!is_int($result)) {
					return false;
				}
			}
			return true;
		}
		return false;
	}

	/**
	 * Extracts the archive contents
	 * @param string $destinationPath Path to location where to extract the files
	 * @param string $file Name of file to extract
	 */
	public function extract(string $destinationPath, string $file = ''): void {
		if ($file === '') {
			$this->zip->extractTo($destinationPath);
		} else {
			$this->zip->extractTo($destinationPath, $file);
		}
	}

	/**
	 * Lists files in the archive
	 * @return array<string> List of files in the archive
	 */
	public function listFiles(): array {
		$files = [];
		for ($i = 0; $i < $this->zip->count(); $i++) {
			$stat = $this->zip->statIndex($i);
			if ($stat === false) {
				continue;
			}
			$name = $stat['name'];
			if (!str_ends_with($name, '/')) {
				$files[] = $name;
			}
		}
		sort($files);
		return $files;
	}

	/**
	 * Opens file in the archive
	 * @param string $fileName File name
	 * @return string Content of file
	 */
	public function openFile(string $fileName): string {
		$content = $this->zip->getFromName($fileName);
		if (!is_bool($content)) {
			return $content;
		}
		return $this->zip->getFromName('/' . $fileName);
	}

	/**
	 * Closes the active archive
	 */
	public function close(): void {
		$this->zip->close();
	}

	/**
	 * Checks if zip archive is empty
	 * @return bool Is zip archive empty?
	 */
	public function isEmpty(): bool {
		$count = 0;
		for ($i = 0; $i < $this->zip->count(); ++$i) {
			if ($this->zip->statIndex($i) !== false) {
				$count++;
			}
		}
		return $count === 0;
	}

}
