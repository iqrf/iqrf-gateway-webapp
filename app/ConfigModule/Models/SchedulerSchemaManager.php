<?php

/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
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

use App\ConfigModule\Exceptions\InvalidTaskMessageException;
use App\CoreModule\Exceptions\InvalidJsonException;
use App\CoreModule\Exceptions\NonexistentJsonSchemaException;
use App\CoreModule\Models\CommandManager;
use App\CoreModule\Models\JsonSchemaManager;
use App\IqrfNetModule\Models\ApiSchemaManager;
use Nette\Utils\JsonException;

/**
 * Scheduler schema manager
 */
class SchedulerSchemaManager extends JsonSchemaManager {

	/**
	 * @var ApiSchemaManager JSON API JSON schema manager
	 */
	private ApiSchemaManager $apiSchemaManager;

	/**
	 * Constructor
	 * @param MainManager $mainManager Main configuration manager
	 * @param CommandManager $commandManager Command manager
	 * @param ApiSchemaManager $apiSchemaManager JSON API JSON schema manager
	 */
	public function __construct(MainManager $mainManager, CommandManager $commandManager, ApiSchemaManager $apiSchemaManager) {
		$cacheDir = $mainManager->getCacheDir();
		if (!is_readable($cacheDir) || !is_writable($cacheDir)) {
			$commandManager->run('chmod 777 ' . $cacheDir, true);
		}
		$configDir = $cacheDir . '/scheduler/schema/';
		parent::__construct($configDir, $commandManager);
		$this->apiSchemaManager = $apiSchemaManager;
	}

	/**
	 * Validates JSON
	 * @param mixed $json JSON to validate
	 * @param bool $tryFix Try fix JSON?
	 * @throws InvalidJsonException
	 * @throws InvalidTaskMessageException
	 * @throws JsonException
	 * @throws NonexistentJsonSchemaException
	 */
	public function validate($json, bool $tryFix = false): void {
		parent::setSchema('schema_cache_record');
		parent::validate($json, $tryFix);
		$tasks = is_array($json->task) ? $json->task : [$json->task];
		foreach ($tasks as $task) {
			if (!is_object($task->message) || !isset($task->message->mType)) {
				throw new InvalidTaskMessageException();
			}
			$this->apiSchemaManager->setSchemaForRequest($task->message->mType);
			$this->apiSchemaManager->validate($task->message);
		}
	}

}
