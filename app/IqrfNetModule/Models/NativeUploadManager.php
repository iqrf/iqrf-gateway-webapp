<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2019 IQRF Tech s.r.o.
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
use App\CoreModule\Exceptions\NonExistingJsonSchemaException;
use App\GatewayModule\Exceptions\CorruptedFileException;
use App\GatewayModule\Exceptions\UnknownFileFormatExceptions;
use App\IqrfNetModule\Enums\UploadFormats;
use App\IqrfNetModule\Exceptions\DpaErrorException;
use App\IqrfNetModule\Exceptions\EmptyResponseException;
use App\IqrfNetModule\Exceptions\UserErrorException;
use Nette\Http\FileUpload;
use Nette\IOException;
use Nette\Utils\FileSystem;
use Nette\Utils\JsonException;
use Nette\Utils\Strings;

/**
 * IQRF TR native upload manager
 */
class NativeUploadManager {

	/**
	 * @var GenericManager Generic configuration manager
	 */
	private $configManager;

	/**
	 * @var string Path to the directory for uploaded files
	 */
	private $path = '/var/cache/iqrf-gateway-daemon/upload';

	/**
	 * @var UploadManager IQRF TR native upload manager
	 */
	private $uploadManager;

	/**
	 * Constructor
	 * @param GenericManager $configManager Generic configuration manager
	 * @param UploadManager $uploadManager IQRF TR native upload manager
	 */
	public function __construct(GenericManager $configManager, UploadManager $uploadManager) {
		$this->configManager = $configManager;
		try {
			$this->configManager->setComponent('iqrf::NativeUploadService');
			$instances = $this->configManager->list();
			if ($instances !== []) {
				$this->path = reset($instances)['uploadPath'];
			}
		} catch (JsonException | NonExistingJsonSchemaException $e) {
			$this->path = '/var/cache/iqrf-gateway-daemon/upload';
		}
		$this->uploadManager = $uploadManager;
	}

	/**
	 * Uploads the file into IQRF TR module
	 * @param FileUpload $file File to upload
	 * @param UploadFormats|null $format File format
	 * @throws CorruptedFileException
	 * @throws DpaErrorException
	 * @throws EmptyResponseException
	 * @throws JsonException
	 * @throws UserErrorException
	 */
	public function upload(FileUpload $file, ?UploadFormats $format = null): void {
		if (!$file->isOk()) {
			throw new CorruptedFileException();
		}
		$this->uploadFile($file->getTemporaryFile(), $format);
	}

	/**
	 * Uploads the file into IQRF TR module
	 * @param string $file File path
	 * @param UploadFormats|null $format File format
	 * @throws DpaErrorException
	 * @throws EmptyResponseException
	 * @throws JsonException
	 * @throws UserErrorException
	 */
	public function uploadFile(string $file, ?UploadFormats $format = null): void {
		FileSystem::createDir($this->path);
		$fileName = basename($file);
		FileSystem::copy($file, $this->path . '/' . $fileName);
		if ($format === null) {
			$format = $this->recognizeFormat($file);
		}
		$this->uploadManager->upload($fileName, $format);
		try {
			FileSystem::delete($this->path . '/' . $fileName);
		} catch (IOException $e) {
			// Do nothing
		}
	}

	/**
	 * Recognizes a file format
	 * @param string $file File name
	 * @return UploadFormats File format
	 * @throws UnknownFileFormatExceptions
	 */
	private function recognizeFormat(string $file): UploadFormats {
		$fileName = Strings::lower($file);
		if (Strings::endsWith($fileName, '.hex')) {
			return UploadFormats::HEX();
		}
		if (Strings::endsWith($fileName, '.iqrf')) {
			return UploadFormats::IQRF();
		}
		if (Strings::endsWith($fileName, '.trcnfg')) {
			return UploadFormats::TRCNFG();
		}
		throw new UnknownFileFormatExceptions();
	}

}
