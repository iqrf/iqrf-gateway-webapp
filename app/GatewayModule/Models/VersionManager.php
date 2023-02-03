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

namespace App\GatewayModule\Models;

use App\CoreModule\Models\CommandManager;
use App\IqrfNetModule\Exceptions\DpaErrorException;
use App\IqrfNetModule\Exceptions\EmptyResponseException;
use App\IqrfNetModule\Models\WebSocketClient;
use App\IqrfNetModule\Requests\ApiRequest;
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
	 * @var ApiRequest IQRF Gateway Daemon's JSON API request
	 */
	private ApiRequest $apiRequest;

	/**
	 * @var CommandManager CommandManager
	 */
	private CommandManager $commandManager;

	/**
	 * @var WebSocketClient WebSocket client
	 */
	private WebSocketClient $wsClient;

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command manager
	 * @param ApiRequest $request IQRF Gateway Daemon's JSON API request
	 * @param WebSocketClient $wsClient WebSocket client
	 */
	public function __construct(CommandManager $commandManager, ApiRequest $request, WebSocketClient $wsClient) {
		$this->commandManager = $commandManager;
		$this->apiRequest = $request;
		$this->wsClient = $wsClient;
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
			} catch (DpaErrorException | EmptyResponseException | JsonException $e) {
				// Use version from CLI
			}
		}
		if ($verbose) {
			return $version;
		}
		return explode(' ', $version)[0] ?? 'unknown';
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
			return Strings::replace($stdout, '#^IQRF\ Gateway\ Daemon\ #', '');
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
			return Strings::replace($result, '#^IQRF\ Gateway\ Setter\ #', '');
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
		return Json::decode($json, Json::FORCE_ARRAY);
	}

	/**
	 * Returns IQRF Gateway Daemon's version
	 * @param bool $verbose Is verbose mode enabled?
	 * @return string IQRF Gateway Webapp's version
	 */
	public function getWebapp(bool $verbose = false): string {
		try {
			$array = $this->getWebappJson();
		} catch (IOException | JsonException $e) {
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

}
