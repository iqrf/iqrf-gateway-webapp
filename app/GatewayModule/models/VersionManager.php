<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2019 IQRF Tech s.r.o.
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
use App\IqrfNetModule\Exceptions\UserErrorException;
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
	private $apiRequest;

	/**
	 * @var CommandManager CommandManager
	 */
	private $commandManager;

	/**
	 * @var WebSocketClient WebSocket client
	 */
	private $wsClient;

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
	 * Returns IQRF Gateway Daemon's version from CLI
	 * @return string IQRF Gateway Daemon's version
	 */
	private function getDaemonCli(): string {
		if (!$this->commandManager->commandExist('iqrfgd2')) {
			return 'none';
		}
		$result = $this->commandManager->run('iqrfgd2 version');
		if ($result !== '') {
			return $result;
		}
		return 'unknown';
	}

	/**
	 * Returns IQRF Gateway Daemon's version from WS client
	 * @return string IQRF Gateway Daemon's version
	 * @throws DpaErrorException
	 * @throws EmptyResponseException
	 * @throws JsonException
	 * @throws UserErrorException
	 */
	private function getDaemonWs(): string {
		$request = [
			'mType' => 'mngDaemon_Version',
			'data' => [
				'returnVerbose' => true,
			],
		];
		$this->apiRequest->setRequest($request);
		$api = $this->wsClient->sendSync($this->apiRequest);
		return $api['response']['data']['rsp']['version'];
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
			} catch (UserErrorException | DpaErrorException | EmptyResponseException | JsonException $e) {
				// Use version from CLI
			}
		}
		if ($verbose) {
			return $version;
		}
		return explode(' ', $version)[0] ?? 'unknown';
	}

	/**
	 * Returns IQRF Gateway Daemon's version
	 * @param bool $verbose Is verbose mode enabled?
	 * @return string IQRF Gateway Webapp's version
	 */
	public function getWebapp(bool $verbose = false): string {
		try {
			$json = FileSystem::read(__DIR__ . '/../../../version.json');
			$array = Json::decode($json, Json::FORCE_ARRAY);
		} catch (IOException | JsonException $e) {
			return 'unknown';
		}
		$version = $array['version'] ?? 'unknown';
		$commit = $array['commit'] ?? '';
		if ($verbose && $commit === '') {
			$isRepo = $this->commandManager->run('git rev-parse --is-inside-work-tree');
			if ($isRepo === 'true') {
				$commit = $this->commandManager->run('git rev-parse --verify HEAD');
			}
		}
		$pipeline = $array['pipeline'] ?? '';
		if ($pipeline === '') {
			return $version . ($verbose ? ' (' . $commit . ')' : '');
		}
		if (Strings::endsWith($version, '-alpha') ||
			Strings::endsWith($version, '-beta') ||
			Strings::endsWith($version, '-dev') ||
			Strings::endsWith($version, '-rc')) {
			return $version . '~' . $array['pipeline'] . ($verbose ? ' (' . $commit . ')' : '');
		}
	}

}
