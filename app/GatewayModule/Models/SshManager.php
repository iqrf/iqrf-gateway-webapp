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

namespace App\GatewayModule\Models;

use App\CoreModule\Models\CommandManager;
use App\CoreModule\Models\FeatureManager;
use App\CoreModule\Models\PrivilegedFileManager;
use App\GatewayModule\Exceptions\SshDirectoryException;
use App\GatewayModule\Exceptions\SshKeyException;

/**
 * SSH manager
 */
class SshManager {

	/**
	 * SSH authorized keys file
	 */
	private const KEYS_FILE = 'authorized_keys';

	/**
	 * @var CommandManager Command manager
	 */
	private $commandManager;

	/**
	 * @var string Path to SSH directory
	 */
	private $directory;

	/**
	 * @var PrivilegedFileManager Privileged file manager
	 */
	private $fileManager;

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command manager
	 * @param FeatureManager $featureManager Feature manager
	 */
	public function __construct(CommandManager $commandManager, FeatureManager $featureManager) {
		$this->commandManager = $commandManager;
		$feature = $featureManager->get('gatewayPass');
		$this->directory = sprintf('/home/%s/.ssh', $feature['user']);
		$this->fileManager = new PrivilegedFileManager($this->directory, $commandManager);
	}

	/**
	 * Returns array of supported key types
	 * @return array<int, string> Array of supported key types
	 */
	public function listKeyTypes(): array {
		$command = $this->commandManager->run('ssh -Q key', true);
		if ($command->getExitCode() !== 0) {
			throw new SshKeyException($command->getStderr());
		}
		return explode(PHP_EOL, $command->getStdout());
	}

	/**
	 * Adds SSH public keys to authorized keys
	 * @param array<int, string> $keys SSH public keys
	 */
	public function addKeys(array $keys): void {
		$this->checkSshDirectory();
		$currentKeys = $this->fileManager->read(self::KEYS_FILE);
		$content = implode(PHP_EOL, $keys);
		if (strlen($currentKeys) === 0) {
			$currentKeys = $content;
		} else {
			$currentKeys .= PHP_EOL . $content;
		}
		$this->fileManager->write(self::KEYS_FILE, $currentKeys);
	}

	/**
	 * Checks if SSH directory exists
	 */
	private function checkSshDirectory(): void {
		$path = $this->directory . '/' . self::KEYS_FILE;
		if (!file_exists($this->directory)) {
			$command = $this->commandManager->run('install -m 755 -d ' . $this->directory, true);
			if ($command->getExitCode() !== 0) {
				throw new SshDirectoryException($command->getStderr());
			}
		}
		if (!file_exists($path)) {
			$command = $this->commandManager->run('touch ' . $path, true);
			if ($command->getExitCode() !== 0) {
				throw new SshDirectoryException($command->getStderr());
			}
			$command = $this->commandManager->run('chmod 644 ' . $path, true);
			if ($command->getExitCode() !== 0) {
				throw new SshDirectoryException($command->getStderr());
			}
		}
	}

}
