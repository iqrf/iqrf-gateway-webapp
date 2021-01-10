<?php

/**
 * Copyright 2020 MICRORISC s.r.o.
 * Copyright 2017-2020 IQRF Tech s.r.o.
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

namespace App\IqrfNetModule\Models;

use App\CoreModule\Entities\ICommand;
use App\CoreModule\Models\CommandManager;
use App\IqrfNetModule\Exceptions\UploadUtilFileException;
use App\IqrfNetModule\Exceptions\UploadUtilMissingException;
use App\IqrfNetModule\Exceptions\UploadUtilSpiException;

/**
 * IQRF Gateway Uploader manager
 */
class UploadUtilManager {

	/**
	 * IQRF Gateway Daemon service name
	 */
	private const DAEMON = 'iqrf-gateway-daemon';

	/**
	 * Path to uploaded files
	 */
	private const UPLOAD_DIR = '/var/cache/iqrf-gateway-daemon/upload/';

	/**
	 * IQRF Gateway Uploader command
	 */
	private const UPLOAD_UTIL = 'iqrf-gateway-uploader';

	/**
	 * @var CommandManager Command manager
	 */
	private $commandManager;

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command manager
	 */
	public function __construct(CommandManager $commandManager) {
		$this->commandManager = $commandManager;
	}

	/**
	 * Uploads file content using the IQRF Gateway Uploader
	 * @param string $name File name
	 * @param string $type File type
	 */
	public function executeUpload(string $name, string $type): void {
		if (!$this->commandManager->commandExist(self::UPLOAD_UTIL)) {
			throw new UploadUtilMissingException('IQRF Gateway Uploader is not installed.');
		}
		if ($type === 'OS') {
			$fileName = str_replace(['(', ')'], ['\(', '\)'], $name);
			$result = $this->commandManager->run(self::UPLOAD_UTIL . ' -I ' . $fileName, true);
		} elseif ($type === 'DPA') {
			$result = $this->commandManager->run(self::UPLOAD_UTIL . ' -I ' . self::UPLOAD_DIR . $name, true);
		} else {
			$result = $this->commandManager->run(self::UPLOAD_UTIL . ' -H ' . self::UPLOAD_DIR . $name, true);
		}
		if ($result->getExitCode() !== 0) {
			$this->handleError($result);
		}
	}

	/**
	 * Handles IQRF Upload Utility errors
	 * @param ICommand $result Command result
	 */
	private function handleError(ICommand $result): void {
		$command = $result->getCommand();
		$exitCode = $result->getExitCode();
		$errorMsg = $result->getStderr();
		if ($exitCode >= 1 && $exitCode <= 5) {
			throw new UploadUtilFileException($errorMsg . ' Command executed: ' . $command);
		}
		throw new UploadUtilSpiException($errorMsg . ' Command executed: ' . $command);
	}

}
