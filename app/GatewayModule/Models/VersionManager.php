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

namespace App\GatewayModule\Models;

use App\IqrfNetModule\Exceptions\DpaErrorException;
use App\IqrfNetModule\Exceptions\EmptyResponseException;
use App\IqrfNetModule\Models\WebSocketClient;
use App\IqrfNetModule\Requests\ApiRequest;
use Iqrf\CommandExecutor\CommandExecutor;
use Nette\IOException;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Nette\Utils\Strings;

/**
 * Version manager
 */
class VersionManager {

	/**
	 * Constructor
	 * @param CommandExecutor $commandManager Command manager
	 * @param ApiRequest $apiRequest IQRF Gateway Daemon's JSON API request
	 * @param WebSocketClient $wsClient WebSocket client
	 */
	public function __construct(
		private readonly CommandExecutor $commandManager,
		private readonly ApiRequest $apiRequest,
		private readonly WebSocketClient $wsClient,
	) {
	}

	/**
	 * Returns versions of all software components
	 * @param bool $verbose Verbose output
	 * @return array<string, string> List of versions of all software components
	 */
	public function getAll(bool $verbose = false): array {
		return [
			'iqrf-cloud-provisioning' => $this->getCloudProvisioning(),
			'iqrf-gateway-controller' => $this->getController(),
			'iqrf-gateway-daemon' => $this->getDaemon($verbose),
			'iqrf-gateway-influxdb-bridge' => $this->getInfluxdbBridge(),
			'iqrf-gateway-setter' => $this->getSetter(),
			'iqrf-gateway-uploader' => $this->getUploader(),
			'iqrf-gateway-webapp' => $this->getWebapp($verbose),
			'mender-client' => $this->getMenderClient(),
			'mender-connect' => $this->getMenderConnect(),
		];
	}

	/**
	 * Returns IQRF Cloud Provisioning's version
	 * @return string|null IQRF Cloud Provisioning's version
	 */
	public function getCloudProvisioning(): ?string {
		if (!$this->commandManager->commandExist('iqrf-cloud-provisioning')) {
			return null;
		}
		$result = $this->commandManager->run('iqrf-cloud-provisioning --version')->getStdout();
		if ($result !== '') {
			return $result;
		}
		return null;
	}

	/**
	 * Returns IQRF Gateway Controller's version
	 * @return string|null IQRF Gateway Controller's version
	 */
	public function getController(): ?string {
		if (!$this->commandManager->commandExist('iqrf-gateway-controller')) {
			return null;
		}
		$result = $this->commandManager->run('iqrf-gateway-controller --version')->getStdout();
		if ($result !== '') {
			return explode(' ', $result)[1];
		}
		return null;
	}

	/**
	 * Returns IQRF Gateway Daemon's version
	 * @param bool $verbose Is verbose mode enabled?
	 * @return string IQRF Gateway Daemon's version
	 */
	public function getDaemon(bool $verbose = false): string {
		$version = $this->getDaemonCli();
		if ($version === 'none' || $version === 'unknown') {
			try {
				$version = $this->getDaemonWs();
			} catch (DpaErrorException | EmptyResponseException) {
				// Use version from CLI
			}
		}
		if ($verbose) {
			return $version;
		}
		return explode(' ', $version)[0];
	}

	/**
	 * Returns IQRF Gateway InfluxDB Bridge's version
	 * @return string|null IQRF Gateway InfluxDB Bridge's version
	 */
	public function getInfluxdbBridge(): ?string {
		if (!$this->commandManager->commandExist('iqrf-gateway-influxdb-bridge')) {
			return null;
		}
		$result = $this->commandManager->run('iqrf-gateway-influxdb-bridge --version')->getStdout();
		if ($result !== '') {
			return Strings::replace($result, '#^IQRF Gateway InfluxDB Bridge #');
		}
		return null;
	}

	/**
	 * Returns Mender client version
	 * @return string|null Mender client version
	 */
	public function getMenderClient(): ?string {
		if ($this->commandManager->commandExist('mender-update')) {
			$command = $this->commandManager->run('mender-update --version');
			if ($command->getExitCode() === 0) {
				return $command->getStdout();
			}
		}
		if ($this->commandManager->commandExist('mender')) {
			$command = $this->commandManager->run('mender --version');
			if ($command->getExitCode() === 0) {
				$versionString = Strings::trim($command->getStdout());
				$pattern = '/^(?\'version\'\d+\.\d+\.\d+).*$/';
				$matches = Strings::match($versionString, $pattern);
				return $matches['version'];
			}
		}
		return null;
	}

	/**
	 * Returns Mender Connect version
	 * @return string|null Mender Connect version
	 */
	public function getMenderConnect(): ?string {
		if (!$this->commandManager->commandExist('mender-connect')) {
			return null;
		}
		$result = $this->commandManager->run('mender-connect --version')->getStdout();
		if ($result !== '') {
			$command = $this->commandManager->run('mender-connect --version');
			if ($command->getExitCode() === 0) {
				$versionString = Strings::trim($command->getStdout());
				$pattern = '/^mender-connect version (?\'version\'\d+\.\d+\.\d+).*$/';
				$matches = Strings::match($versionString, $pattern);
				return $matches['version'];
			}
		}
		return null;
	}

	/**
	 * Returns IQRF Gateway Setter's version
	 * @return string|null IQRF Gateway Setter's version
	 */
	public function getSetter(): ?string {
		if (!$this->commandManager->commandExist('iqrf-gateway-setter')) {
			return null;
		}
		$result = $this->commandManager->run('iqrf-gateway-setter --version')->getStdout();
		if ($result !== '') {
			return Strings::replace($result, '#^IQRF Gateway Setter #');
		}
		return null;
	}

	/**
	 * Returns IQRF Gateway Uploader's version
	 * @return string|null IQRF Gateway Uploader's version
	 */
	public function getUploader(): ?string {
		if (!$this->commandManager->commandExist('iqrf-gateway-uploader')) {
			return null;
		}
		$result = $this->commandManager->run('iqrf-gateway-uploader --version')->getStdout();
		if ($result !== '') {
			return $result;
		}
		return null;
	}

	/**
	 * Returns IQRF Gateway Daemon's version as JSON
	 * @return array<string, string> IQRF Gateway Webapp version JSON
	 * @throws IOException
	 * @throws JsonException
	 */
	public function getWebappJson(): array {
		$json = FileSystem::read(__DIR__ . '/../../../version.json');
		return Json::decode($json, forceArrays: true);
	}

	/**
	 * Returns IQRF Gateway Daemon's version
	 * @param bool $verbose Is verbose mode enabled?
	 * @return string IQRF Gateway Webapp's version
	 */
	public function getWebapp(bool $verbose = false): string {
		try {
			$array = $this->getWebappJson();
		} catch (IOException | JsonException) {
			return 'unknown';
		}
		$version = $array['version'] ?? 'unknown';
		$commit = $array['commit'] ?? '';
		if ($verbose && $commit === '') {
			$isRepo = $this->commandManager->run('git rev-parse --is-inside-work-tree');
			if ($isRepo->getExitCode() === 0 && $isRepo->getStdout() === 'true') {
				$commit = $this->commandManager->run('git rev-parse --verify HEAD')->getStdout();
			}
		}
		$pipeline = $array['pipeline'] ?? '';
		if (
			$pipeline !== '' &&
			Strings::match($version, '#^[A-Za-z0-9.]*\-(alpha|beta|dev|rc)[A-Za-z0-9]*$#i') !== null
		) {
			$version .= '~' . $pipeline;
		}
		return $version . ($verbose && $commit !== '' ? ' (' . $commit . ')' : '');
	}

	/**
	 * Returns IQRF Gateway Daemon's version from CLI
	 * @return string IQRF Gateway Daemon's version
	 */
	private function getDaemonCli(): string {
		if (!$this->commandManager->commandExist('iqrfgd2')) {
			return 'none';
		}
		$command = $this->commandManager->run('iqrfgd2 version');
		$stdout = $command->getStdout();
		if ($command->getExitCode() === 0 && $stdout !== '') {
			return $stdout;
		}
		$command = $this->commandManager->run('iqrfgd2 --version');
		$stdout = $command->getStdout();
		if ($command->getExitCode() === 0 && $stdout !== '') {
			return Strings::replace($stdout, '#^IQRF Gateway Daemon #');
		}
		return 'unknown';
	}

	/**
	 * Returns IQRF Gateway Daemon's version from WS client
	 * @return string IQRF Gateway Daemon's version
	 * @throws DpaErrorException
	 * @throws EmptyResponseException
	 */
	private function getDaemonWs(): string {
		$request = [
			'mType' => 'mngDaemon_Version',
			'data' => [
				'returnVerbose' => true,
			],
		];
		$this->apiRequest->set($request);
		$api = $this->wsClient->sendSync($this->apiRequest);
		return $api['response']->data->rsp->version;
	}

}
