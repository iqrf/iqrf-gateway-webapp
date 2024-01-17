<?php

/**
 * Copyright 2017-2022 IQRF Tech s.r.o.
 * Copyright 2019-2022 MICRORISC s.r.o.
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
use App\MaintenanceModule\Exceptions\MenderFailedException;
use App\MaintenanceModule\Exceptions\MenderMissingException;
use App\MaintenanceModule\Exceptions\MenderNoUpdateInProgressException;
use App\MaintenanceModule\Exceptions\MountErrorException;
use App\ServiceModule\Models\ServiceManager;
use Nette\Utils\FileSystem;
use Nette\Utils\JsonException;
use Nette\Utils\Strings;
use Psr\Http\Message\UploadedFileInterface;
use z4kn4fein\SemVer\SemverException;
use z4kn4fein\SemVer\Version;

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
	 * @var ServiceManager $serviceManager Service manager
	 */
	private $serviceManager;

	/**
	 * @var Version $menderVersion Mender client version
	 */
	private $menderVersion;

	/**
	 * Constructior
	 * @param CommandManager $commandManager Command manager
	 * @param JsonFileManager $fileManager JSON file manager
	 */
	public function __construct(CommandManager $commandManager, JsonFileManager $fileManager, ServiceManager $serviceManager) {
		$this->commandManager = $commandManager;
		$this->fileManager = $fileManager;
		$this->serviceManager = $serviceManager;
		$this->menderVersion = $this->getMenderVersion();
	}

	/**
	 * Returns mender configuration
	 * @return array<string, int|string> current mender configuration
	 * @throws JsonException
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
	 * @throws JsonException
	 */
	public function getClientConfig(): array {
		return $this->fileManager->read(self::CLIENT_CONF, true, '.conf');
	}

	/**
	 * Reads mender-connect config file
	 * @return array<string, string|array<string, int|bool>> current mender-connect configuration
	 * @throws JsonException
	 */
	public function getConnectConfig(): array {
		return $this->fileManager->read(self::CONNECT_CONF, true, '.conf');
	}

	/**
	 * Saves updated mender-client configuration file
	 * @param array<string, int|string> $newConfig Mender configuration
	 * @throws JsonException
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
	 * @throws MountErrorException
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
	 * @param string $filePath Path to uploaded file
	 */
	public function removeArtifactFile(string $filePath): void {
		FileSystem::delete($filePath);
	}

	/**
	 * Installs update from artifact and returns output
	 * @param string $filePath Path to mender artifact file
	 * @return string Output log
	 * @throws MenderFailedException
	 * @throws MenderNoUpdateInProgressException
	 * @throws MenderMissingException
	 */
	public function installArtifact(string $filePath): string {
		$this->stopService();
		$result = $this->commandManager->run(
			sprintf('mender %s %s 2>&1', $this->getCommandOption('install'), $filePath),
			true,
			1800
		);
		return $this->handleCommandResult($result->getExitCode(), $result->getStdout(), $filePath);
	}

	/**
	 * Commits installed mender artifact
	 * @return string Output log
	 * @throws MenderFailedException
	 * @throws MenderNoUpdateInProgressException
	 * @throws MenderMissingException
	 */
	public function commitUpdate(): string {
		$this->stopService();
		$result = $this->commandManager->run(
			sprintf('mender %s 2>&1', $this->getCommandOption('commit')),
			true
		);
		return $this->handleCommandResult($result->getExitCode(), $result->getStdout());
	}

	/**
	 * Rolls installed mender artifact back
	 * @return string Output log
	 * @throws MenderFailedException
	 * @throws MenderNoUpdateInProgressException
	 * @throws MenderMissingException
	 */
	public function rollbackUpdate(): string {
		$this->stopService();
		$result = $this->commandManager->run(
			sprintf('mender %s 2>&1', $this->getCommandOption('rollback')),
			true
		);
		return $this->handleCommandResult($result->getExitCode(), $result->getStdout());
	}

	/**
	 * Checks execution status and processes output log accordingly
	 * @param int $code Mender execution code
	 * @param string $output Output log before processing
	 * @param string|null $filePath Path to file to remove
	 * @return string Processed output log
	 * @throws MenderFailedException
	 * @throws MenderNoUpdateInProgressException
	 */
	private function handleCommandResult(int $code, string $output, ?string $filePath = null): string {
		if ($filePath !== null) {
			$this->removeArtifactFile($filePath);
		}
		$lines = explode(PHP_EOL, $output);
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
		$output = trim(implode(PHP_EOL, $lines));
		if ($code === 0) {
			return $output;
		}
		if ($code === 2) {
			throw new MenderNoUpdateInProgressException($output);
		}
		throw new MenderFailedException($output);
	}

	/**
	 * Checks if Mender utility is installed
	 * @throws MenderMissingException If mender is not installed
	 */
	private function checkMender(): void {
		if (!$this->commandManager->commandExist('mender')) {
			throw new MenderMissingException('Mender utility is not installed.');
		}
	}

	/**
	 * Stops mender-client service if active
	 */
	private function stopService(): void {
		$this->checkMender();
		if ($this->serviceManager->isActive('mender-client')) {
			$this->serviceManager->stop('mender-client');
		}
	}

	/**
	 * Stores mender-client version
	 * @throws MenderFailedException If mender-client version command fails
	 * @throws SemverException If version parsing fails
	 */
	private function getMenderVersion(): Version {
		$this->checkMender();
		$command = $this->commandManager->run('mender --version');
		if ($command->getExitCode() !== 0) {
			throw new MenderFailedException($command->getStderr());
		}
		$versionString = Strings::trim($command->getStdout());
		$pattern = '/^(?\'version\'\d+\.\d+\.\d+).*$/';
		$matches = Strings::match($versionString, $pattern);
		if ($matches === null || $matches['version'] === null) {
			throw new MenderFailedException('Failed to parse Mender client version string: ' . $versionString);
		}
		return Version::parse($matches['version']);
	}

	/**
	 * Construct command option string
	 * @param string $option Command option
	 * @return string Processed command option
	 */
	private function getCommandOption(string $option): string {
		if ($this->menderVersion->getMajor() >= 3) {
			return $option;
		}
		return '-' . $option;
	}

}
