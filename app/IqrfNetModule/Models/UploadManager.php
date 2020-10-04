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
use App\CoreModule\Exceptions\NonexistentJsonSchemaException;
use App\GatewayModule\Exceptions\UnknownFileFormatExceptions;
use App\IqrfNetModule\Enums\UploadFormats;
use Nette\Utils\FileSystem;
use Nette\Utils\JsonException;
use Nette\Utils\Strings;

/**
 * IQRF TR native upload manager
 */
class UploadManager {

	/**
	 * @var string Path to the directory for uploaded files
	 */
	private $path = '/var/cache/iqrf-gateway-daemon/upload';

	/**
	 * Constructor
	 * @param GenericManager $configManager Generic configuration manager
	 */
	public function __construct(GenericManager $configManager) {
		try {
			$configManager->setComponent('iqrf::NativeUploadService');
			$instances = $configManager->list();
			if (isset($instances[0]['uploadPath'])) {
				$this->path = $instances[0]['uploadPath'];
			}
		} catch (JsonException | NonexistentJsonSchemaException $e) {
			$this->path = '/var/cache/iqrf-gateway-daemon/upload';
		}
	}

	/**
	 * Uploads the file into IQRF TR module
	 * @param string $fileName $fileName
	 * @param string $fileContent file content
	 * @param UploadFormats|null $format File format
	 * @return array<string> file name and file format
	 */
	public function uploadFile(string $fileName, string $fileContent, ?UploadFormats $format = null): array {
		if ($format === null) {
			$format = $this->recognizeFormat($fileName);
		}
		FileSystem::createDir($this->path);
		FileSystem::write($this->path . '/' . $fileName, $fileContent);
		return ['fileName' => $fileName, 'format' => $format->toScalar()];
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
