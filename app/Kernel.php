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

namespace App;

use Nette\Configurator;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder;
use Nette\Utils\Json;
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
		$configurator->setDebugMode(false);
		$configurator->enableTracy(__DIR__ . '/../log');
		$configurator->setTimeZone('Europe/Prague');
		$configurator->setTempDirectory(__DIR__ . '/../temp');
		$configurator->createRobotLoader()->addDirectory(__DIR__)->register();
		$configurator->addConfig(__DIR__ . '/config/config.neon');
		$version = Json::decode(FileSystem::read(__DIR__ . '/../version.json'));
		$configurator->addParameters([
			'sentry' => [
				'release' => $version->version . ($version->pipeline !== '' ? '~' . $version->pipeline : ''),
			],
		]);
		/**
		 * @var SplFileInfo $file File info object
		 */
		foreach (Finder::findFiles('*Module/config/config.neon')->from(__DIR__) as $file) {
			$configurator->addConfig($file->getRealPath());
		}
		return $configurator;
	}

}
