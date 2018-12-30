<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2018 IQRF Tech s.r.o.
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

namespace App\ConfigModule\Models;

use App\ConfigModule\Exceptions\InvalidConfigurationFormatException;
use DateTime;
use Nette\Application\BadRequestException;
use Nette\Application\Responses\FileResponse;
use Nette\IOException;
use Nette\SmartObject;
use Nette\Utils\JsonException;

/**
 * Scheduler configuration migration manager
 */
class SchedulerMigrationManager {

	use SmartObject;

	/**
	 * @var MainManager Main configuration manager
	 */
	private $mainConfigManager;

	/**
	 * @var string File name (without .json)
	 */
	private $fileName = 'Tasks';

	/**
	 * @var string Path to a directory with scheduler's configuration
	 */
	private $path;

	/**
	 * Constructor
	 * @param MainManager $mainManager Main configuration manager
	 */
	public function __construct(MainManager $mainManager) {
		$this->mainConfigManager = $mainManager;
		try {
			$this->path = $this->mainConfigManager->load()['cacheDir'] . '/scheduler/';
		} catch (IOException | JsonException $e) {
			$this->path = '/var/cache/iqrfgd2/scheduler/';
		}
	}

	/**
	 * Download a scheduler's configuration
	 * @return FileResponse HTTP response with a scheduler's configuration
	 * @throws BadRequestException
	 */
	public function download(): FileResponse {
		$now = new DateTime();
		$fileName = 'iqrf-gateway-scheduler' . $now->format('c') . '.json';
		$path = $this->path . '/' . $this->fileName . '.json';
		$contentType = 'application/json';
		$response = new FileResponse($path, $fileName, $contentType, true);
		return $response;
	}

	/**
	 * Upload a scheduler's configuration
	 * @param mixed[] $formValues Values from form
	 * @throws InvalidConfigurationFormatException
	 */
	public function upload(array $formValues): void {
		$json = $formValues['configuration'];
		if (!$json->isOk()) {
			throw new InvalidConfigurationFormatException();
		}
		$type = $json->getContentType();
		if ($type !== 'application/json' && $type !== 'text/plain') {
			throw new InvalidConfigurationFormatException();
		}
		$path = $this->path . '/' . $this->fileName . '.json';
		$json->move($path);
	}

}
