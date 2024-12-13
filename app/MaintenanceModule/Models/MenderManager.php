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

namespace App\MaintenanceModule\Models;

use App\CoreModule\Models\PrivilegedFileManager;
use App\GatewayModule\Models\VersionManager;
use App\MaintenanceModule\Entities\MenderClientConfiguration;
use App\MaintenanceModule\Entities\MenderConnectConfiguration;
use App\MaintenanceModule\Enums\MenderClientActions;
use App\MaintenanceModule\Exceptions\MenderFailedException;
use App\MaintenanceModule\Exceptions\MenderMissingException;
use App\MaintenanceModule\Exceptions\MenderNoUpdateInProgressException;
use App\MaintenanceModule\Exceptions\MountErrorException;
use App\ServiceModule\Exceptions\NonexistentServiceException;
use App\ServiceModule\Exceptions\UnsupportedInitSystemException;
use App\ServiceModule\Models\ServiceManager;
use Iqrf\CommandExecutor\CommandExecutor;
use Nette\Utils\FileSystem;
use Nette\Utils\JsonException;
use Nette\Utils\Strings;
use Psr\Http\Message\UploadedFileInterface;
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
	private const CLIENT_CONF = 'mender.conf';

	/**
	 * JSON file containing mender-connect configuration
	 */
	private const CONNECT_CONF = 'mender-connect.conf';

	/**
	 * Path to upload artifact file to
	 */
	private const UPLOAD_PATH = '/tmp/';

	/**
	 * @var Version|null $clientVersion Mender client version
	 */
	private readonly ?Version $clientVersion;

	/**
	 * @var Version|null $connectVersion Mender connect version
	 */
	private readonly ?Version $connectVersion;

	/**
	 * Constructor
	 * @param CommandExecutor $commandManager Command manager
	 * @param PrivilegedFileManager $fileManager Privileged file manager
	 * @param ServiceManager $serviceManager Service manager
	 * @param VersionManager $versionManager Version manager
	 */
	public function __construct(
		private readonly CommandExecutor $commandManager,
		private readonly PrivilegedFileManager $fileManager,
		private readonly ServiceManager $serviceManager,
		VersionManager $versionManager,
	) {
		$clientVersion = $versionManager->getMenderClient();
		$connectVersion = $versionManager->getMenderConnect();
		$this->clientVersion = ($clientVersion !== null) ? Version::parseOrNull($clientVersion) : null;
		$this->connectVersion = ($connectVersion !== null) ? Version::parseOrNull($connectVersion) : null;
	}

	/**
	 * Returns Mender configuration
	 * @return array<string, array{version: int, config: array<string, mixed>}> current Mender configuration
	 * @throws JsonException
	 */
	public function getConfig(): array {
		$this->checkMender();
		return [
			'client' => $this->getClientConfig(),
			'connect' => $this->getConnectConfig(),
		];
	}

	/**
	 * Reads Mender client config file
	 * @param bool $raw Return raw configuration?
	 * @return array{config: array<string, mixed>, version: int} Mender client configuration
	 * @throws JsonException
	 */
	public function getClientConfig(bool $raw = false): array {
		$this->checkMender();
		$config = $this->fileManager->readJson(self::CLIENT_CONF);
		if ($raw) {
			return $config;
		}
		return MenderClientConfiguration::configDeserialize($this->clientVersion->getMajor(), $config)->jsonSerialize();
	}

	/**
	 * Reads Mender connect config file
	 * @param bool $raw Return raw configuration?
	 * @return array{config: array<string, mixed>, version: int} Mender connect configuration
	 * @throws JsonException
	 */
	public function getConnectConfig(bool $raw = false): array {
		$this->checkMender();
		$config = $this->fileManager->readJson(self::CONNECT_CONF);
		if ($raw) {
			return $config;
		}
		return MenderConnectConfiguration::configDeserialize($this->connectVersion->getMajor(), $config)->jsonSerialize();
	}

	/**
	 * Saves updated mender-client configuration file
	 * @param array{client: array{config: array<string, mixed>, version: int}, connect: array{config: array<string, mixed>, version: int}} $config Mender configuration
	 * @throws JsonException
	 */
	public function saveConfig(array $config): void {
		$this->checkMender();
		$client = array_replace_recursive($this->getClientConfig(true), MenderClientConfiguration::jsonDeserialize($config['client'])->configSerialize());
		$client = MenderClientConfiguration::configFixUp($this->clientVersion->getMajor(), $client);
		$connect = array_replace_recursive($this->getConnectConfig(true), MenderConnectConfiguration::jsonDeserialize($config['connect'])->configSerialize());
		$this->fileManager->writeJson(self::CLIENT_CONF, $client);
		$this->fileManager->writeJson(self::CONNECT_CONF, $connect);
	}

	/**
	 * Saves uploaded certificate file and returns full path
	 * @param string $fileName File name
	 * @param string $content File contents
	 * @return string Path to uploaded certificate
	 */
	public function saveCertFile(string $fileName, string $content): string {
		$this->checkMender();
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
		$this->checkMender();
		$filePath = self::UPLOAD_PATH . $file->getClientFilename();
		$file->moveTo($filePath);
		return $filePath;
	}

	/**
	 * Removes uploaded artifact file
	 * @param string $filePath Path to uploaded file
	 */
	public function removeArtifactFile(string $filePath): void {
		$this->checkMender();
		FileSystem::delete($filePath);
	}

	/**
	 * Installs update from artifact and returns output
	 * @param string $filePath Path to Mender artifact file
	 * @return string Output log
	 * @throws MenderFailedException
	 * @throws MenderNoUpdateInProgressException
	 * @throws MenderMissingException
	 */
	public function installArtifact(string $filePath): string {
		$this->checkMender();
		$this->stopService();
		$result = $this->commandManager->run(
			$this->getCommand(MenderClientActions::Install, $filePath),
			true,
			1800
		);
		return $this->handleCommandResult($result->getExitCode(), $result->getStdout(), $filePath);
	}

	/**
	 * Commits installed Mender artifact
	 * @return string Output log
	 * @throws MenderFailedException
	 * @throws MenderMissingException
	 * @throws MenderNoUpdateInProgressException
	 * @throws NonexistentServiceException
	 * @throws UnsupportedInitSystemException
	 */
	public function commitUpdate(): string {
		$this->checkMender();
		$this->stopService();
		$result = $this->commandManager->run(
			$this->getCommand(MenderClientActions::Commit),
			true
		);
		return $this->handleCommandResult($result->getExitCode(), $result->getStdout());
	}

	/**
	 * Rolls installed Mender artifact back
	 * @return string Output log
	 * @throws MenderFailedException
	 * @throws MenderMissingException
	 * @throws MenderNoUpdateInProgressException
	 * @throws NonexistentServiceException
	 * @throws UnsupportedInitSystemException
	 */
	public function rollbackUpdate(): string {
		$this->checkMender();
		$this->stopService();
		$result = $this->commandManager->run($this->getCommand(MenderClientActions::Rollback), true);
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
				$matchIdx = strpos($line, $matches[0]);
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
	 * @throws MenderMissingException If Mender is not installed
	 */
	private function checkMender(): void {
		if (!$this->clientVersion instanceof Version) {
			throw new MenderMissingException('Mender utility is not installed.');
		}
	}

	/**
	 * Stops Mender client service if active
	 * @throws NonexistentServiceException
	 * @throws UnsupportedInitSystemException
	 */
	private function stopService(): void {
		$service = ($this->clientVersion->getMajor() >= 4) ? 'mender-updated' : 'mender-client';
		if ($this->serviceManager->isActive($service)) {
			$this->serviceManager->stop($service);
		}
	}

	/**
	 * Constructs Mender client command to execute
	 * @param MenderClientActions $action Action to execute
	 * @param string|null $arg Optional argument
	 * @return string Constructed command
	 */
	private function getCommand(MenderClientActions $action, ?string $arg = null): string {
		$args = [
			($this->clientVersion->getMajor() >= 4) ? 'mender-update' : 'mender',
			($this->clientVersion->getMajor() < 3 ? '-' : '') . $action->value,
		];
		if ($arg !== null) {
			$args[] = escapeshellarg($arg);
		}
		return implode(' ', $args) . ' 2>&1';
	}

}
