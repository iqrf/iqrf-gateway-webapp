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

namespace App\MaintenanceModule\Models;

use App\CoreModule\Models\CommandManager;
use App\CoreModule\Models\JsonFileManager;
use App\MaintenanceModule\Exceptions\MenderMissingException;
use App\MaintenanceModule\Exceptions\MountErrorException;
use App\ServiceModule\Models\ServiceManager;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Nette\Utils\Strings;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Mender client configuration manager
 */
class MenderManager {

	/**
	 * Path to certificate storage
	 */
	private const CERT_PATH = '/etc/mender/';

	/**
	 * JSON file containing mender-client configuration
	 */
	private const CLIENT_CONF = 'mender';

	/**
	 * JSON file containing mender-connect configuration
	 */
	private const CONNECT_CONF = 'mender-connect';

	/**
	 * Path to upload artifact file to
	 */
	private const UPLOAD_PATH = '/tmp/';

	/**
	 * @var CommandManager Command manager
	 */
	private $commandManager;

	/**
	 * @var JsonFileManager $fileManager JSON file manager
	 */
	private $fileManager;

	/**
	 * @var MenderQueue Mender message queue
	 */
	private $menderQueue;

	/**
	 * @var ServiceManager $serviceManager Service manager
	 */
	private $serviceManager;

	/**
	 * @var string|null $filePath Path to artifact file
	 */
	private $filePath;

	/**
	 * Constructior
	 * @param CommandManager $commandManager Command manager
	 * @param JsonFileManager $fileManager JSON file manager
	 */
	public function __construct(CommandManager $commandManager, JsonFileManager $fileManager, MenderQueue $menderQueue, ServiceManager $serviceManager) {
		$this->commandManager = $commandManager;
		$this->fileManager = $fileManager;
		$this->menderQueue = $menderQueue;
		$this->serviceManager = $serviceManager;
	}

	/**
	 * Returns mender configuration
	 * @return array<string, int|string> current mender configuration
	 */
	public function getConfig(): array {
		$clientConfig = $this->getClientConfig();
		$connectConfig = $this->getConnectConfig();
		$clientConfig['ClientProtocol'] = $connectConfig['ClientProtocol'] ?? 'https';
		return $clientConfig;
	}

	/**
	 * Reads mender-client config file
	 * @return array<string, int|string> current mender-client configuration
	 */
	public function getClientConfig(): array {
		return $this->fileManager->read(self::CLIENT_CONF, true, '.conf');
	}

	/**
	 * Reads mender-connect config file
	 * @return array<string, string|array<string, int|bool>> current mender-connect configuration
	 */
	public function getConnectConfig(): array {
		return $this->fileManager->read(self::CONNECT_CONF, true, '.conf');
	}

	/**
	 * Saves updated mender-client configuration file
	 * @param array<string, int|string> $newConfig Mender configuration
	 */
	public function saveConfig(array $newConfig): void {
		$clientConfig = $this->getClientConfig();
		$connectConfig = $this->getConnectConfig();
		$connectConfig['ServerURL'] = $newConfig['ServerURL'];
		$connectConfig['ClientProtocol'] = $newConfig['ClientProtocol'];
		unset($newConfig['ClientProtocol']);
		$this->fileManager->write(self::CLIENT_CONF, array_merge($clientConfig, $newConfig), '.conf');
		$this->fileManager->write(self::CONNECT_CONF, $connectConfig, '.conf');
	}

	/**
	 * Saves uploaded certificate file and returns full path
	 * @param string $fileName File name
	 * @param string $content File contents
	 * @return string Path to uploaded certificate
	 */
	public function saveCertFile(string $fileName, string $content): string {
		$filePath = self::CERT_PATH . $fileName;
		FileSystem::write($filePath, $content);
		return $filePath;
	}

	/**
	 * Remounts root filesystem with specified mode
	 * @param string $mode Mount mode
	 */
	public function remount(string $mode): void {
		$output = $this->commandManager->run('mount -o remount,' . $mode . ' /', true);
		if ($output->getExitCode() !== 0) {
			throw new MountErrorException($output->getStderr());
		}
	}

	/**
	 * Saves uploaded artifact file and returns full path
	 * @param UploadedFileInterface $file Uploaded file
	 * @return string Path to uploaded file artifact
	 */
	public function saveArtifactFile(UploadedFileInterface $file): string {
		$filePath = self::UPLOAD_PATH . $file->getClientFilename();
		$file->moveTo($filePath);
		return $filePath;
	}

	/**
	 * Removes uploaded artifact file
	 */
	public function removeArtifactFile(): void {
		if ($this->filePath !== null) {
			FileSystem::delete($this->filePath);
			$this->filePath = null;
		}
	}

	/**
	 * Installs update from artifact and returns output
	 * @param string $filePath Path to mender artifact file
	 */
	public function installArtifact(string $filePath): void {
		$this->checkMender();
		$this->filePath = $filePath;
		$this->commandManager->runAsync(function (string $type, ?string $buffer): void {
			$message = $this->handleCommandResult($type, $buffer);
			$this->menderQueue->publish($message);
		}, 'mender install ' . $filePath . ' 2>&1', true, 1800);
	}

	/**
	 * Commits installed mender artifact
	 */
	public function commitUpdate(): void {
		$this->checkMender();
		$this->commandManager->runAsync(function (string $type, ?string $buffer): void {
			$message = $this->handleCommandResult($type, $buffer);
			$this->menderQueue->publish($message);
		}, 'mender -commit 2>&1', true);
	}

	/**
	 * Rolls installed mender artifact back
	 */
	public function rollbackUpdate(): void {
		$this->checkMender();
		$this->commandManager->runAsync(function (string $type, ?string $buffer): void {
			$message = $this->handleCommandResult($type, $buffer);
			$this->menderQueue->publish($message);
		}, 'mender -rollback 2>&1', true);
	}

	/**
	 * Checks execution status and processes output log accordingly
	 * @param string $type Stream type
	 * @param string $content Content
	 * @return string Processed
	 */
	private function handleCommandResult(string $type, string $content): string {
		$message = [
			'messageType' => $type,
		];
		if ($type === 'exit') {
			$message['payload'] = $content;
			return Json::encode($message);
		}
		$lines = explode(PHP_EOL, $content);
		$pattern = '/time="([0-9T+:\-]+)"\slevel=(debug|info|warning|error|fatal|panic)\smsg="([^"]+)"/';
		foreach ($lines as $idx => $line) {
			$matches = Strings::match($line, $pattern);
			if ($matches === null) {
				continue;
			}
			$matchLen = strlen($matches[0]);
			$lines[$idx] = sprintf('%s - [%s]: %s', $matches[1], $matches[2], $matches[3]);
			if (strlen($line) !== $matchLen) {
				$matchIdx = strpos($line, $matches[0], 0);
				$prefix = substr($line, 0, $matchIdx);
				$suffix = substr($line, $matchIdx + $matchLen);
				$lines[$idx] = trim($prefix . PHP_EOL . $lines[$idx] . PHP_EOL . $suffix);
			}
		}
		$message['payload'] = trim(implode(PHP_EOL, $lines));
		return Json::encode($message);
	}

	/**
	 * Checks if Mender utility is installed, stops mender-client if active
	 */
	private function checkMender(): void {
		if (!$this->commandManager->commandExist('mender')) {
			throw new MenderMissingException('Mender utility is not installed.');
		}
		if ($this->serviceManager->isActive('mender-client')) {
			$this->serviceManager->stop('mender-client');
		}
	}

}
