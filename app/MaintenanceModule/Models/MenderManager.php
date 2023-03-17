<?php

/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
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
use App\CoreModule\Models\PrivilegedFileManager;
use App\MaintenanceModule\Exceptions\MenderFailedException;
use App\MaintenanceModule\Exceptions\MenderMissingException;
use App\MaintenanceModule\Exceptions\MenderNoUpdateInProgressException;
use App\MaintenanceModule\Exceptions\MountErrorException;
use App\ServiceModule\Exceptions\NonexistentServiceException;
use App\ServiceModule\Exceptions\UnsupportedInitSystemException;
use App\ServiceModule\Models\ServiceManager;
use Nette\Utils\FileSystem;
use Nette\Utils\JsonException;
use Nette\Utils\Strings;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Mender client configuration manager
 */
class MenderManager {

	/**
	 * @var string Path to certificate storage
	 */
	private const CERT_PATH = '/etc/mender/';

	/**
	 * @var string JSON file containing mender-client configuration
	 */
	private const CLIENT_CONF = 'mender.conf';

	/**
	 * @var string JSON file containing mender-connect configuration
	 */
	private const CONNECT_CONF = 'mender-connect.conf';

	/**
	 * @var string Path to upload artifact file to
	 */
	private const UPLOAD_PATH = '/tmp/';

	/**
	 * @var array<string, mixed> Default client configuration
	 */
	private const DEFAULT_CLIENT_CONFIG = [
		'ServerURL' => 'https://hosted.mender.io',
		'ServerCertificate' => '',
		'TenantToken' => 'dummy',
		'InventoryPollIntervalSeconds' => 28800,
		'RetryPollIntervalSeconds' => 300,
		'UpdatePollIntervalSeconds' => 1800,
	];

	/**
	 * @var array<string, array<string, bool>> Default connect configuration
	 */
	private const DEFAULT_CONNECT_CONFIG = [
		'FileTransfer' => [
			'Disable' => false,
		],
		'PortForward' => [
			'Disable' => false,
		],
	];

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command manager
	 * @param PrivilegedFileManager $fileManager Privileged file manager
	 * @param ServiceManager $serviceManager Service manager
	 */
	public function __construct(
		private readonly CommandManager $commandManager,
		private readonly PrivilegedFileManager $fileManager,
		private readonly ServiceManager $serviceManager,
	) {
	}

	/**
	 * Returns mender configuration
	 * @return array<string, array<string, mixed>> current mender configuration
	 * @throws JsonException
	 */
	public function getConfig(): array {
		$client = array_replace_recursive(self::DEFAULT_CLIENT_CONFIG, $this->getClientConfig());
		$connect = array_replace_recursive(self::DEFAULT_CONNECT_CONFIG, $this->getConnectConfig());
		return [
			'client' => [
				'ServerURL' => $client['ServerURL'],
				'ServerCertificate' => $client['ServerCertificate'],
				'TenantToken' => $client['TenantToken'],
				'InventoryPollIntervalSeconds' => $client['InventoryPollIntervalSeconds'],
				'RetryPollIntervalSeconds' => $client['RetryPollIntervalSeconds'],
				'UpdatePollIntervalSeconds' => $client['UpdatePollIntervalSeconds'],
			],
			'connect' => [
				'FileTransfer' => !$connect['FileTransfer']['Disable'],
				'PortForward' => !$connect['PortForward']['Disable'],
			],
		];
	}

	/**
	 * Reads mender-client config file
	 * @return array<string, mixed> current mender-client configuration
	 * @throws JsonException
	 */
	public function getClientConfig(): array {
		return $this->fileManager->readJson(self::CLIENT_CONF);
	}

	/**
	 * Reads mender-connect config file
	 * @return array<string, mixed> current mender-connect configuration
	 * @throws JsonException
	 */
	public function getConnectConfig(): array {
		return $this->fileManager->readJson(self::CONNECT_CONF);
	}

	/**
	 * Saves updated mender-client configuration file
	 * @param array<string, array<string, bool|int|string>> $config Mender configuration
	 * @throws JsonException
	 */
	public function saveConfig(array $config): void {
		$client = $this->getClientConfig();
		$connect = $this->getConnectConfig();
		$this->fileManager->writeJson(self::CLIENT_CONF, array_replace_recursive($client, $config['client']));
		$config['connect'] = [
			'FileTransfer' => ['Disable' => !$config['connect']['FileTransfer']],
			'PortForward' => ['Disable' => !$config['connect']['PortForward']],
		];
		$this->fileManager->writeJson(self::CONNECT_CONF, array_replace_recursive($connect, $config['connect']));
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
		$this->checkMender();
		$result = $this->commandManager->run('mender -install ' . escapeshellarg($filePath) . ' 2>&1', true, 1800);
		return $this->handleCommandResult($result->getExitCode(), $result->getStdout(), $filePath);
	}

	/**
	 * Commits installed mender artifact
	 * @return string Output log
	 * @throws MenderFailedException
	 * @throws MenderMissingException
	 * @throws MenderNoUpdateInProgressException
	 * @throws NonexistentServiceException
	 * @throws UnsupportedInitSystemException
	 */
	public function commitUpdate(): string {
		$this->checkMender();
		$result = $this->commandManager->run('mender -commit 2>&1', true);
		return $this->handleCommandResult($result->getExitCode(), $result->getStdout());
	}

	/**
	 * Rolls installed mender artifact back
	 * @return string Output log
	 * @throws MenderFailedException
	 * @throws MenderMissingException
	 * @throws MenderNoUpdateInProgressException
	 * @throws NonexistentServiceException
	 * @throws UnsupportedInitSystemException
	 */
	public function rollbackUpdate(): string {
		$this->checkMender();
		$result = $this->commandManager->run('mender -rollback 2>&1', true);
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
	 * Checks if Mender utility is installed, stops mender-client if active
	 * @throws MenderMissingException
	 * @throws NonexistentServiceException
	 * @throws UnsupportedInitSystemException
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
