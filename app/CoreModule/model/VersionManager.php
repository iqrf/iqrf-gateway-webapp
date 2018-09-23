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

namespace App\CoreModule\Model;

use App\CoreModule\Model\CommandManager;
use App\CoreModule\Model\JsonFileManager;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Nette;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Utils\Json;

/**
 * Tool for getting versions of IQRF Gateway Daemon and IQRF Gateway Daemon webapp
 */
class VersionManager {

	use Nette\SmartObject;

	/**
	 * @var Cache Cache
	 */
	private $cache;

	/**
	 * @var Client HTTP(S) client
	 */
	private $client;

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
	 * @param IStorage $storage Cache storage
	 * @param Client $client HTTP(S) client
	 */
	public function __construct(CommandManager $commandManager, IStorage $storage, Client $client) {
		$this->cache = new Cache($storage, get_class($this));
		$this->client = $client;
		$this->commandManager = $commandManager;
		$this->jsonFileManager = new JsonFileManager(__DIR__ . '/../../../');
	}

	/**
	 * Check if an update is available for the webapp
	 * @return bool Is available an update for the webapp?
	 */
	public function availableWebappUpdate(): bool {
		return version_compare($this->getInstalledWebapp(false), $this->getCurrentWebapp(), '<');
	}

	/**
	 * Get the current stable version of the webapp
	 * @return string Current stable version of the webapp
	 */
	public function getCurrentWebapp(): string {
		$json = $this->cache->load('current', function (&$dependencies) {
			$dependencies = [Cache::EXPIRE => '1 hour'];
			$repoUrl = 'https://raw.githubusercontent.com/iqrfsdk/iqrf-gateway-webapp/';
			try {
				$url = $repoUrl . 'stable/version.json';
				$file = $this->client->request('GET', $url)->getBody()->getContents();
			} catch (BadResponseException $e) {
				$url = $repoUrl . 'master/version.json';
				$file = $this->client->request('GET', $url)->getBody()->getContents();
			}
			return $file;
		});
		return trim(Json::decode($json, Json::FORCE_ARRAY)['version'], 'v');
	}

	/**
	 * Get the installed version of the webapp
	 * @param bool $verbose Is verbose mode enabled?
	 * @return string Installed version of the webapp
	 */
	public function getInstalledWebapp(bool $verbose = true): string {
		$file = $this->jsonFileManager->read('version');
		$version = $file['version'];
		if (!$verbose) {
			return trim($version, 'v');
		}
		if (isset($file['commit']) && $file['commit'] !== '') {
			return $version . ' (' . $file['commit'] . ')';
		}
		$isRepo = $this->commandManager->send('git rev-parse --is-inside-work-tree');
		if ($isRepo === 'true') {
			$commit = $this->commandManager->send('git rev-parse --verify HEAD');
			return $version . ' (' . $commit . ')';
		}
		return $version;
	}

}
