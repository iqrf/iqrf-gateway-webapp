<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2018 IQRF Tech s.r.o.
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

namespace App\Model;

use App\Model\JsonFileManager;
use GuzzleHttp\Client;
use Nette;
use Nette\Utils\Json;

/**
 * Tool for getting versions of IQRF Gateway Daemon and IQRF Gateway Daemon webapp
 */
class VersionManager {

	use Nette\SmartObject;

	/**
	 * @var CommandManager Command manager
	 */
	private $commandManager;

	/**
	 * @var JsonFileManager JSON file manager
	 */
	private $jsonFileManager;

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command manager
	 */
	public function __construct(CommandManager $commandManager) {
		$this->commandManager = $commandManager;
		$this->jsonFileManager = new JsonFileManager(__DIR__ . '/../../');
	}

	/**
	 * Check if an update is available for the webapp
	 * @return bool Is available an update for the webapp?
	 */
	public function availableWebappUpdate() {
		return version_compare($this->getInstalledWebapp(false), $this->getCurrentWebapp(), '<');
	}

	/**
	 * Get the current stable version of the webapp
	 * @return string Current stable version of the webapp
	 */
	public function getCurrentWebapp() {
		$client = new Client();
		$composerUrl = 'https://raw.githubusercontent.com/iqrfsdk/iqrf-gateway-webapp/stable/composer.json';
		$composerJson = $client->request('GET', $composerUrl)->getBody()->getContents();
		return Json::decode($composerJson, Json::FORCE_ARRAY)['version'];
	}

	/**
	 * Get the installed version of the webapp
	 * @param bool $verbose Is verbose mode enabled?
	 * @return string Installed version of the webapp
	 */
	public function getInstalledWebapp(bool $verbose = true) {
		$composer = $this->jsonFileManager->read('composer')['version'];
		if ($verbose) {
			$composer = 'v' . $composer;
		}
		if ($verbose && $this->commandManager->commandExist('git')) {
			$branches = $this->commandManager->send('git branch -v --no-abbrev');
			if (preg_match('{^\* (.+?)\s+([a-f0-9]{40})(?:\s|$)}m', $branches, $matches)) {
				$composer .= ' (' . $matches[1] . ' - ' . $matches[2] . ')';
			}
		}
		return $composer;
	}

}
