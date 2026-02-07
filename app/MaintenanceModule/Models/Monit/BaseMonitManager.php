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

namespace App\MaintenanceModule\Models\Monit;

use App\MaintenanceModule\Exceptions\MonitConfigErrorException;
use Iqrf\CommandExecutor\CommandExecutor;
use Iqrf\FileManager\IFileManager;
use Nette\Utils\Strings;

/**
 * Base Monit configuration manager
 */
abstract class BaseMonitManager {

	/**
	 * Available configuration directory
	 */
	protected const CONFIG_AVAILABLE = 'conf-available';

	/**
	 * Enabled configuration directory
	 */
	protected const CONFIG_ENABLED = 'conf-enabled';

	/**
	 * Constructor
	 * @param IFileManager $fileManager Privileged file manager
	 * @param CommandExecutor $commandManager Command manager
	 */
	public function __construct(
		protected IFileManager $fileManager,
		protected CommandExecutor $commandManager,
	) {
	}

	/**
	 * Checks if Monit configuration is available
	 * @param string $fileName Configuration file name
	 * @return bool Is configuration file available?
	 */
	protected function isConfigFileAvailable(string $fileName): bool {
		return $this->fileManager->exists(self::CONFIG_AVAILABLE . '/' . $fileName);
	}

	/**
	 * Checks if Monit configuration is enabled
	 * @param string $fileName Configuration file name
	 * @return bool Is configuration file enabled?
	 */
	protected function isConfigFileEnabled(string $fileName): bool {
		return $this->fileManager->isSymLink(self::CONFIG_ENABLED . '/' . $fileName);
	}

	/**
	 * Enables Monit configuration file
	 * @param string $fileName Configuration file name
	 * @param bool $state Enable or disable configuration file
	 */
	protected function enableConfigFile(string $fileName, bool $state): void {
		if ($state) {
			$this->fileManager->chmod(self::CONFIG_AVAILABLE . '/' . $fileName, 0600);
			if (!$this->validateConfig(self::CONFIG_AVAILABLE . '/' . $fileName)) {
				throw new MonitConfigErrorException('Configuration file \'' . $fileName . '\' is not valid.');
			}
			$this->fileManager->createSymLink(self::CONFIG_AVAILABLE . '/' . $fileName, self::CONFIG_ENABLED . '/' . $fileName);
		} else {
			$this->fileManager->delete(self::CONFIG_ENABLED . '/' . $fileName);
		}
	}

	/**
	 * Reads configuration file
	 * @param string $fileName Configuration file name
	 * @param bool $withComments Include comments in the result
	 * @return array<string> Configuration file content
	 */
	protected function readConfigFile(string $fileName, bool $withComments): array {
		$fileContent = $this->fileManager->read($fileName);
		$array = explode(PHP_EOL, $fileContent);
		if ($withComments) {
			return $array;
		}
		return array_filter($array, static fn (string $item): bool => !str_starts_with($item, '#'));
	}

	/**
	 * Reads and parses Monit configuration line
	 * @param string $fileName Configuration file name
	 * @param string $pattern Pattern to match with named groups
	 * @return array<string, string> Parsed configuration line
	 * @throws MonitConfigErrorException
	 */
	protected function readConfigLine(string $fileName, string $pattern): array {
		foreach ($this->readConfigFile($fileName, false) as $item) {
			$matches = Strings::match($item, $pattern);
			if ($matches !== null) {
				return array_filter($matches, static fn ($key): bool => is_string($key), ARRAY_FILTER_USE_KEY);
			}
		}
		throw new MonitConfigErrorException('Monit configuration file contains invalid content.');
	}

	/**
	 * Writes Monit configuration line into the configuration file
	 * @param string $fileName Configuration file name
	 * @param string $pattern Pattern to match
	 * @param string $newContent New content to save
	 * @throws MonitConfigErrorException Monit configuration file contains invalid content
	 */
	protected function writeConfigLine(string $fileName, string $pattern, string $newContent): void {
		$config = $this->readConfigFile($fileName, true);
		$origConfig = $config;
		$lineIndex = -1;
		foreach ($config as $lineNumber => $line) {
			$matches = Strings::match($line, $pattern);
			if ($matches !== null) {
				$lineIndex = $lineNumber;
				break;
			}
		}
		if ($lineIndex === -1) {
			throw new MonitConfigErrorException('Monit configuration file contains invalid content.');
		}
		$config[$lineIndex] = $newContent;
		if (end($config) !== '') {
			$config[] = '';
		}
		$this->fileManager->write($fileName, implode(PHP_EOL, $config));
		$this->fileManager->chmod($fileName, 0600);
		if (!$this->validateConfig($fileName)) {
			$this->fileManager->write($fileName, implode(PHP_EOL, $origConfig));
			$this->fileManager->chmod($fileName, 0600);
			throw new MonitConfigErrorException('Monit configuration file does not pass validation.');
		}
	}

	/**
	 * Validates Monit configuration file
	 * @param string|null $configFile Configuration file name to validate
	 * @return bool Is configuration file valid?
	 */
	protected function validateConfig(?string $configFile = null): bool {
		$path = realpath($this->fileManager->getBasePath() . '/' . ($configFile ?? ''));
		if ($path === false) {
			return false;
		}
		$command = $this->commandManager->run('monit -t' . ($configFile !== null ? (' -c ' . escapeshellarg($path)) : ''), true);
		return $command->getExitCode() === 0;
	}

}
