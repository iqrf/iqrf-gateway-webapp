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

namespace App\IqrfNetModule\Models;

use App\ConfigModule\Models\GenericManager;
use App\ConfigModule\Models\MainManager;
use App\CoreModule\Exceptions\NonexistentJsonSchemaException;
use App\GatewayModule\Exceptions\UnknownFileFormatExceptions;
use App\IqrfNetModule\Enums\UploadFormats;
use App\IqrfNetModule\Exceptions\UploaderFileException;
use App\IqrfNetModule\Exceptions\UploaderMissingException;
use App\IqrfNetModule\Exceptions\UploaderSpiException;
use Iqrf\CommandExecutor\CommandExecutor;
use Iqrf\CommandExecutor\ICommand;
use Nette\Utils\FileSystem;
use Nette\Utils\JsonException;
use Nette\Utils\Strings;

/**
 * IQRF TR native upload manager
 */
class UploadManager {

	/**
	 * Path to OS patch files
	 */
	final public const OS_PATH = __DIR__ . '/../../../iqrf/os/';

	/**
	 * IQRF Gateway Uploader command
	 */
	private const UPLOADER = 'iqrf-gateway-uploader';

	/**
	 * @var string Path to the directory for uploaded files
	 */
	private string $path = '/var/cache/iqrf-gateway-daemon/upload/';

	/**
	 * Constructor
	 * @param CommandExecutor $commandManager Command manager
	 * @param GenericManager $genericManager Generic daemon component manager
	 * @param MainManager $mainManager Main daemon configuration manager
	 */
	public function __construct(
		private readonly CommandExecutor $commandManager,
		GenericManager $genericManager,
		MainManager $mainManager,
	) {
		try {
			$cacheDir = $mainManager->getCacheDir();
			if (!str_ends_with($cacheDir, '/')) {
				$cacheDir .= '/';
			}
			$genericManager->setComponent('iqrf::OtaUploadService');
			$instances = $genericManager->list();
			if (isset($instances[0]['uploadPathSuffix'])) {
				$uploadDir = $instances[0]['uploadPathSuffix'];
			}
			if (!isset($uploadDir) || $uploadDir === '') {
				$uploadDir = 'upload';
			}
			if (!str_ends_with($uploadDir, '/')) {
				$uploadDir .= '/';
			}
			$this->path = Strings::replace($cacheDir . $uploadDir, '~/+~', '/');
		} catch (JsonException | NonexistentJsonSchemaException) {
			$this->path = '/var/cache/iqrf-gateway-daemon/upload/';
		}
	}

	/**
	 * Uploads file to daemon cache directory
	 * @param string $fileName File name
	 * @param string $fileContent File content
	 * @param UploadFormats|null $format File format
	 * @return array{fileName: string, format: string} file name and file format
	 */
	public function uploadToFs(string $fileName, string $fileContent, ?UploadFormats $format = null): array {
		if (!$format instanceof UploadFormats) {
			$format = $this->recognizeFormat($fileName);
		}
		FileSystem::createDir($this->path);
		FileSystem::write($this->path . $fileName, $fileContent);
		return ['fileName' => $fileName, 'format' => $format->value];
	}

	/**
	 * Uploads plugin to transceiver via IQRF Gateway Uploader
	 * @param string $fileName File name
	 * @param bool $os Indicates upload of OS file
	 * @param UploadFormats|null $format File format
	 * @throws UploaderFileException
	 * @throws UploaderMissingException
	 * @throws UploaderSpiException
	 */
	public function uploadToTr(string $fileName, bool $os = false, ?UploadFormats $format = null): void {
		if (!$this->commandManager->commandExist(self::UPLOADER)) {
			throw new UploaderMissingException('IQRF Gateway Uploader is not installed.');
		}
		if (!$format instanceof UploadFormats) {
			$format = $this->recognizeFormat($fileName);
		}
		$path = escapeshellarg(($os ? self::OS_PATH : $this->path) . $fileName);
		$command = sprintf('%s %s %s', self::UPLOADER, $format->getUploaderParameter(), $path);
		$result = $this->commandManager->run($command, true);
		if ($result->getExitCode() !== 0) {
			$this->handleError($result);
		}
	}

	/**
	 * Handles IQRF Gateway Uploader errors
	 * @param ICommand $result Command result
	 * @throws UploaderFileException
	 * @throws UploaderSpiException
	 */
	private function handleError(ICommand $result): void {
		$exitCode = $result->getExitCode();
		$errorMsg = $result->getStderr();
		if ($exitCode >= 1 && $exitCode <= 5) {
			throw new UploaderFileException($errorMsg);
		}
		throw new UploaderSpiException($errorMsg);
	}

	/**
	 * Recognizes a file format
	 * @param string $file File name
	 * @return UploadFormats File format
	 * @throws UnknownFileFormatExceptions
	 */
	private function recognizeFormat(string $file): UploadFormats {
		$fileName = Strings::lower($file);
		if (str_ends_with($fileName, '.hex')) {
			return UploadFormats::HEX;
		}
		if (str_ends_with($fileName, '.iqrf')) {
			return UploadFormats::IQRF;
		}
		if (str_ends_with($fileName, '.trcnfg')) {
			return UploadFormats::TRCNFG;
		}
		throw new UnknownFileFormatExceptions();
	}

}
