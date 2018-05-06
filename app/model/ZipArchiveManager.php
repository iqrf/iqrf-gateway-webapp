<?php

/**
 * Copyright 2018 IQRF Tech s.r.o.
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
 * Tool for creating a new zip archive
 */
class ZipArchiveManager {

	use Nette\SmartObject;

	/**
	 * @var \ZipArchive
	 */
	private $zip;

	/**
	 * Constructor
	 * @param string $path Path of a new zip archive
	 */
	public function __construct(string $path) {
		$this->zip = new \ZipArchive();
		$this->zip->open($path, \ZipArchive::CREATE);
	}

	/**
	 * Add a file to a ZIP archive from the given path
	 * @param string $path The path to the file to add
	 * @param string $filename FIle name in the archive
	 */
	public function addFile(string $path, string $filename) {
		$this->zip->addFile($path, $filename);
	}

	/**
	 * Add a file to a ZIP archive using its contents
	 * @param string $filename File name
	 * @param string $content The content of text file
	 */
	public function addFileFromText(string $filename, string $content) {
		$this->zip->addFromString($filename, $content);
	}

	/**
	 * Add a JSON file to a ZIP archive using its contents
	 * @param string $filename File name
	 * @param array $jsonData JSON data in an array
	 */
	public function addJsonFromArray(string $filename, array $jsonData) {
		$json = Json::encode($jsonData, Json::PRETTY);
		$this->zip->addFromString($filename, $json);
	}

	/**
	 * Close the active archive
	 */
	public function close() {
		$this->zip->close();
	}

}
