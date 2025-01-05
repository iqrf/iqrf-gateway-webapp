<?php

/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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

namespace App\CoreModule\Models;

use App\GatewayModule\Models\VersionManager as GatewayVersionManager;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use Nette\Caching\Cache;
use Nette\Caching\Storage;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

/**
 * Tool for getting versions of IQRF Gateway Daemon and IQRF Gateway Daemon webapp
 */
class VersionManager {

	/**
	 * @var Cache Cache
	 */
	private Cache $cache;

	/**
	 * @var ClientInterface HTTP(S) client
	 */
	private ClientInterface $client;

	/**
	 * @var GatewayVersionManager Gateway version manager
	 */
	private GatewayVersionManager $versionManager;

	/**
	 * Constructor
	 * @param Storage $storage Cache storage
	 * @param ClientInterface $client HTTP(S) client
	 * @param GatewayVersionManager $versionManager Gateway version manager
	 */
	public function __construct(Storage $storage, ClientInterface $client, GatewayVersionManager $versionManager) {
		$this->cache = new Cache($storage, 'version_manager');
		$this->client = $client;
		$this->versionManager = $versionManager;
	}

	/**
	 * Checks if an update is available for the webapp
	 * @return bool Is available an update for the webapp?
	 * @throws JsonException
	 */
	public function availableWebappUpdate(): bool {
		$installedVersion = $this->versionManager->getWebapp(false);
		$currentVersion = $this->getCurrentWebapp();
		print_r([$installedVersion, $currentVersion]);
		return version_compare($installedVersion, $currentVersion, '<');
	}

	/**
	 * Gets the current stable version of the webapp
	 * @return string Current stable version of the webapp
	 * @throws JsonException
	 */
	public function getCurrentWebapp(): string {
		$json = $this->cache->load('current', function (&$dependencies): string {
			$dependencies = [Cache::EXPIRE => '1 hour'];
			$repoUrl = 'https://gitlab.iqrf.org/open-source/iqrf-gateway-webapp/raw/';
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

}
