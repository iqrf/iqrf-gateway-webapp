<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017 IQRF Tech s.r.o.
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

namespace App\GatewayModule\Model;

use Nette;
use Nette\Application\Responses\FileResponse;
use Nette\Utils\FileSystem;

class LogManager {

	use Nette\SmartObject;

	/**
	 * @var string
	 */
	private $path;

	/**
	 * Constructor
	 * @param string $path Path to iqrf-daemon log
	 */
	public function __construct($path) {
		$this->path = $path;
	}

	/**
	 * Load log of iqrf-daemon
	 * @return string iqrf-daemon log
	 */
	public function load() {
		return FileSystem::read($this->path);
	}

	/**
	 * Download lof iqrf-daemon
	 * @return FileResponse HTTP response with the log
	 */
	public function download() {
		$fileName = 'iqrf-daemon.log';
		$contentType = 'text/plain';
		$response = new FileResponse($this->path, $fileName, $contentType, true);
		return $response;
	}

}
