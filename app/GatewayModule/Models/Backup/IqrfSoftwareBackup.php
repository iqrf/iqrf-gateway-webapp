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

namespace App\GatewayModule\Models\Backup;

use App\CoreModule\Models\FeatureManager;
use App\CoreModule\Models\ZipArchiveManager;
use InvalidArgumentException;
use Iqrf\CommandExecutor\CommandExecutor;
use Iqrf\FileManager\FileManager;

/**
 * IQRF Software backup manager
 */
abstract class IqrfSoftwareBackup implements IBackupManager {

	/**
	 * IQRF Gateway Controller
	 */
	protected const IQRF_GATEWAY_CONTROLLER = 'IQRF Gateway Controller';

	/**
	 * IQRF Gateway Translator
	 */
	protected const IQRF_GATEWAY_TRANSLATOR = 'IQRF Gateway Translator';

	/**
	 * IQRF Gateway Uploader
	 */
	protected const IQRF_GATEWAY_UPLOADER = 'IQRF Gateway Uploader';

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
	protected bool $featureEnabled;

	/**
	 * @var string Software name
	 */
	private readonly string $software;

	/**
	 * Constructor
	 * @param string $software Software name
	 * @param FileManager $fileManager File manager
	 * @param CommandExecutor $commandExecutor Command manager
	 * @param FeatureManager $featureManager Feature manager
	 * @param RestoreLogger $restoreLogger Restore logger
	 */
	public function __construct(
		string $software,
		private readonly FileManager $fileManager,
		private readonly CommandExecutor $commandExecutor,
		FeatureManager $featureManager,
		private readonly RestoreLogger $restoreLogger,
	) {
		if (!in_array($software, self::SOFTWARES, true)) {
			throw new InvalidArgumentException('Invalid software name.');
		}
		$this->software = $software;
		$this->featureEnabled = $featureManager->isEnabled($this->getFeatureName());
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
	 * Recreates directory
	 */
	private function recreateDirectory(): void {
		$owner = PosixHelper::getChownOwner();
		$path = escapeshellarg($this->fileManager->getBasePath());
		$this->commandExecutor->run('rm -rf ' . $path, true);
		$this->commandExecutor->run('mkdir ' . $path, true);
		$this->commandExecutor->run('chown -R ' . $owner . ' ' . $path, true);
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
		};
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
		};
	}

}
