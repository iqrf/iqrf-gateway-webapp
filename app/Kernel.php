<?php

/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
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
use Nette\Utils\FileInfo;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

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
		$tempDir = __DIR__ . '/../temp';
		$configurator->setTempDirectory($tempDir);
		FileSystem::createDir($tempDir . '/sessions');
		$configurator->createRobotLoader()->addDirectory(__DIR__)->register();
		$confDir = __DIR__ . '/config';
		$configurator->addConfig($confDir . '/config.neon');
		self::setVersionParameters($configurator);
		$configurator->addStaticParameters(['confDir' => $confDir]);
		try {
			$iqrfRepositoryManager = new IqrfRepositoryManager($confDir . '/iqrf-repository.neon');
			$configurator->addDynamicParameters(['iqrfRepository' => $iqrfRepositoryManager->readConfig()]);
		} catch (IOException | Exception) {
			// File not found/is corrupted - do nothing
		}
		/**
		 * @var FileInfo $file File info object
		 */
		foreach (Finder::findFiles('*Module/config/config.neon')->from(__DIR__) as $file) {
			$configurator->addConfig($file->getRealPath());
		}
		if (!defined('EMAIL_VALIDATE_DNS')) {
			$container = $configurator->createContainer();
			define('EMAIL_VALIDATE_DNS', $container->getParameters()['emailValidateDns']);
		}
		return $configurator;
	}

	/**
	 * Sets version parameters
	 * @param Configurator $configurator Nette DI initial configurator
	 */
	private static function setVersionParameters(Configurator $configurator): void {
		try {
			$versionInfo = Json::decode(FileSystem::read(__DIR__ . '/../version.json'));
			$version = $versionInfo->version . ($versionInfo->pipeline !== '' ? '~' . $versionInfo->pipeline : '');
		} catch (IOException | JsonException) {
			$version = 'unknown';
		} finally {
			$configurator->addStaticParameters([
				'console' => [
					'version' => $version,
				],
				'sentry' => [
					'release' => $version,
				],
			]);
		}
	}

}
