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

namespace App;

use App\ConfigModule\Models\IqrfRepositoryManager;
use Nette\Bootstrap\Configurator;
use Nette\IOException;
use Nette\Neon\Exception;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use SplFileInfo;

/**
 * Application's kernel
 */
class Kernel {

	/**
	 * Boots the application's kernel
	 * @return Configurator Configurator
	 */
	public static function boot(): Configurator {
		$configurator = new Configurator();
		$configurator->enableTracy(__DIR__ . '/../log');
		$configurator->setTimeZone('Europe/Prague');
		$tempDir = __DIR__ . '/../temp';
		$configurator->setTempDirectory($tempDir);
		FileSystem::createDir($tempDir . '/sessions');
		$configurator->createRobotLoader()->addDirectory(__DIR__)->register();
		$confDir = __DIR__ . '/config';
		$configurator->addConfig($confDir . '/config.neon');
		try {
			$version = Json::decode(FileSystem::read(__DIR__ . '/../version.json'));
			$configurator->addParameters([
				'sentry' => [
					'release' => $version->version . ($version->pipeline !== '' ? '~' . $version->pipeline : ''),
				],
			]);
		} catch (IOException | JsonException $e) {
			// Skip Sentry version settings
		}
		$configurator->addParameters(['confDir' => $confDir]);
		try {
			$iqrfRepositoryManager = new IqrfRepositoryManager($confDir . '/iqrf-repository.neon');
			$configurator->addDynamicParameters(['iqrfRepository' => $iqrfRepositoryManager->readConfig()]);
		} catch (IOException | Exception $e) {
			// File not found/is corrupted - do nothing
		}
		/**
		 * @var SplFileInfo $file File info object
		 */
		foreach (Finder::findFiles('*Module/config/config.neon')->from(__DIR__) as $file) {
			$configurator->addConfig($file->getRealPath());
		}
		return $configurator;
	}

}
