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

namespace App\GatewayModule\Models\Backup;

use App\CoreModule\Models\CommandManager;
use App\CoreModule\Models\FeatureManager;
use App\CoreModule\Models\FileManager;
use App\CoreModule\Models\ZipArchiveManager;
use InvalidArgumentException;

/**
 * IQRF Software backup manager
 */
abstract class IqrfSoftwareBackup implements IBackupManager {

	/**
	 * IQRF Gateway Controller
	 */
	final protected const IQRF_GATEWAY_CONTROLLER = 'IQRF Gateway Controller';

	/**
	 * IQRF Gateway Translator
	 */
	final protected const IQRF_GATEWAY_TRANSLATOR = 'IQRF Gateway Translator';

	/**
	 * IQRF Gateway Uploader
	 */
	final protected const IQRF_GATEWAY_UPLOADER = 'IQRF Gateway Uploader';

	/**
	 * List of whitelisted pieces of software
	 */
	private const SOFTWARES = [
		self::IQRF_GATEWAY_CONTROLLER,
		self::IQRF_GATEWAY_TRANSLATOR,
		self::IQRF_GATEWAY_UPLOADER,
	];

	/**
	 * @var bool Indicates whether feature is enabled
	 */
	private readonly bool $featureEnabled;

	/**
	 * Constructor
	 * @param string $software Software name
	 * @param FileManager $fileManager File manager
	 * @param CommandManager $commandManager Command manager
	 * @param FeatureManager $featureManager Feature manager
	 * @param RestoreLogger $restoreLogger Restore logger
	 */
	public function __construct(
		private readonly string $software,
		private readonly FileManager $fileManager,
		private readonly CommandManager $commandManager,
		FeatureManager $featureManager,
		private readonly RestoreLogger $restoreLogger,
	) {
		if (!in_array($software, self::SOFTWARES, true)) {
			throw new InvalidArgumentException('Invalid software name.');
		}
		$this->featureEnabled = $featureManager->get($this->getFeatureName())['enabled'];
	}

	/**
	 * Returns service names
	 * @return array<string> Service names
	 */
	abstract public function getServices(): array;

	/**
	 * Performs IQRF software backup
	 * @param array<string, array<string, bool>> $params Request parameters
	 * @param ZipArchiveManager $zipManager ZIP archive manager
	 */
	public function backup(array $params, ZipArchiveManager $zipManager): void {
		if (!$params['software']['iqrf'] || !$this->featureEnabled) {
			return;
		}
		if ($this->fileManager->exists('')) {
			$zipManager->addFolder($this->fileManager->getBasePath(), $this->zipDir());
		}
	}

	/**
	 * Performs IQRF software restore
	 * @param ZipArchiveManager $zipManager ZIP archive manager
	 */
	public function restore(ZipArchiveManager $zipManager): void {
		if (!$zipManager->exist($this->zipDir() . '/') || !$this->featureEnabled) {
			return;
		}
		$this->restoreLogger->log('Restoring ' . $this->software . ' configuration.');
		$this->recreateDirectory();
		$this->fileManager->write('config.json', $zipManager->openFile($this->zipDir() . '/config.json'));
	}

	/**
	 * Returns optional feature name
	 * @return string Optional feature name
	 */
	private function getFeatureName(): string {
		return match ($this->software) {
			self::IQRF_GATEWAY_CONTROLLER => 'iqrfGatewayController',
			self::IQRF_GATEWAY_TRANSLATOR => 'iqrfGatewayTranslator',
			self::IQRF_GATEWAY_UPLOADER => 'trUpload',
			default => throw new InvalidArgumentException('Invalid software name.'),
		};
	}

	/**
	 * Recreates directory
	 */
	private function recreateDirectory(): void {
		$user = posix_getpwuid(posix_geteuid());
		$owner = $user['name'] . ':' . posix_getgrgid($user['gid'])['name'];
		$path = escapeshellarg($this->fileManager->getBasePath());
		$this->commandManager->run('rm -rf ' . $path, true);
		$this->commandManager->run('mkdir ' . $path, true);
		$this->commandManager->run('chown -R ' . $owner . ' ' . $path, true);
	}

	/**
	 * Returns to directory path in the ZIP archive
	 * @return string Directory path in the ZIP archive
	 */
	private function zipDir(): string {
		return match ($this->software) {
			self::IQRF_GATEWAY_CONTROLLER => 'controller',
			self::IQRF_GATEWAY_TRANSLATOR => 'translator',
			self::IQRF_GATEWAY_UPLOADER => 'uploader',
			default => throw new InvalidArgumentException('Invalid software name.'),
		};
	}

}
