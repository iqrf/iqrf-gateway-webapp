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

namespace App\GatewayModule\Models\Backup;

use App\CoreModule\Models\ZipArchiveManager;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Nette\Utils\Strings;

/**
 * Gateway file backup manager
 */
class GatewayFileBackup {

	/**
	 * Gateway files whitelist
	 */
	public const WHITELIST = [
		'iqrf-gateway.json',
	];

	/**
	 * Path to configuration directory
	 */
	private const CONF_PATH = '/etc/';

	/**
	 * Path to MqttMessaging component configuration
	 */
	private const MQTT_PATH = '/etc/iqrf-gateway-daemon/iqrf__MqttMessaging.json';

	/**
	 * Client ID pattern
	 */
	private const GWID_PATTERN = '/^[a-f0-9]{16}$/';

	/**
	 * MQTT request topic pattern
	 */
	private const REQUEST_TOPIC_PATTERN = '/^gateway\\/[a-z0-9]{16}\\/iqrf\\/requests$/';

	/**
	 * MQTT response topic pattern
	 */
	private const RESPONSE_TOPIC_PATTERN = '/^gateway\\/[a-z0-9]{16}\\/iqrf\\/responses$/';

	/**
	 * Path to JsonSplitter component configuration
	 */
	private const SPLITTER_PATH = '/etc/iqrf-gateway-daemon/iqrf__JsonSplitter.json';

	/**
	 * @var string Gateway ID
	 */
	private $gwId;

	/**
	 * @var string Gateway token
	 */
	private $gwToken;

	/**
	 * @var ZipArchiveManager ZIP archive manager
	 */
	private $zipManager;

	/**
	 * Constructor
	 * @param string $gwId Gateway ID
	 * @param string $gwToken Gateway token
	 * @param ZipArchiveManager $zipManager ZIP archive manager
	 */
	public function __construct(string $gwId, string $gwToken, ZipArchiveManager $zipManager) {
		$this->gwId = Strings::lower($gwId);
		$this->gwToken = $gwToken;
		$this->zipManager = $zipManager;
	}

	/**
	 * Performs gateway file backup
	 * @param array<string, array<string, bool>> $params Request parameters
	 * @param array<string, bool> $services Array of services
	 */
	public function backup(array $params, ?array &$services = null): void {
		if (file_exists(self::CONF_PATH . 'iqrf-gateway.json')) {
			$this->zipManager->addFile(self::CONF_PATH . 'iqrf-gateway.json', 'gateway/iqrf-gateway.json');
		}
	}

	/**
	 * Performs gateway file restore
	 */
	public function restore(): void {
		if (!$this->zipManager->exist('gateway/')) {
			return;
		}
		if (file_exists(self::MQTT_PATH)) {
			$config = Json::decode(FileSystem::read(self::MQTT_PATH), Json::FORCE_ARRAY);
			if (Strings::match($config['ClientId'], self::GWID_PATTERN) !== null) {
				$config['ClientId'] = $this->gwId;
			}
			if (Strings::match($config['TopicRequest'], self::REQUEST_TOPIC_PATTERN) !== null) {
				$config['TopicRequest'] = 'gateway/' . $this->gwId . '/iqrf/requests';
			}
			if (Strings::match($config['TopicResponse'], self::RESPONSE_TOPIC_PATTERN) !== null) {
				$config['TopicResponse'] = 'gateway/' . $this->gwId . '/iqrf/responses';
			}
			FileSystem::write(self::MQTT_PATH, Json::encode($config));
		}
		if (file_exists(self::SPLITTER_PATH)) {
			$config = Json::decode(FileSystem::read(self::SPLITTER_PATH), Json::FORCE_ARRAY);
			$config['insId'] = $this->gwToken;
			FileSystem::write(self::SPLITTER_PATH, Json::encode($config));
		}
	}

}
