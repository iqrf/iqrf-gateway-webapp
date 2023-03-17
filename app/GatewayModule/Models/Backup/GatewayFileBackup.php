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

namespace App\GatewayModule\Models\Backup;

use App\CoreModule\Models\ZipArchiveManager;
use App\GatewayModule\Models\Utils\GatewayInfoUtil;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Nette\Utils\Strings;

/**
 * Gateway file backup manager
 */
class GatewayFileBackup implements IBackupManager {

	/**
	 * @var array<string> Gateway files whitelist
	 */
	final public const WHITELIST = [
		'iqrf-gateway.json',
	];

	/**
	 * @var string Path to configuration directory
	 */
	private const CONF_PATH = '/etc/';

	/**
	 * @var string Path to MqttMessaging component configuration
	 */
	private const MQTT_PATH = '/etc/iqrf-gateway-daemon/iqrf__MqttMessaging.json';

	/**
	 * @var string Client ID pattern
	 */
	private const GWID_PATTERN = '/^[a-f0-9]{16}$/';

	/**
	 * @var string MQTT request topic pattern
	 */
	private const REQUEST_TOPIC_PATTERN = '/^gateway\\/[a-z0-9]{16}\\/iqrf\\/requests$/';

	/**
	 * @var string MQTT response topic pattern
	 */
	private const RESPONSE_TOPIC_PATTERN = '/^gateway\\/[a-z0-9]{16}\\/iqrf\\/responses$/';

	/**
	 * @var string Path to JsonSplitter component configuration
	 */
	private const SPLITTER_PATH = '/etc/iqrf-gateway-daemon/iqrf__JsonSplitter.json';

	/**
	 * @var string Gateway ID
	 */
	private readonly string $gwId;

	/**
	 * Constructor
	 * @param GatewayInfoUtil $gwInfo Gateway information utility
	 */
	public function __construct(
		GatewayInfoUtil $gwInfo,
		private readonly RestoreLogger $restoreLogger,
	) {
		$this->gwId = Strings::lower($gwInfo->getId());
	}

	/**
	 * Performs gateway file backup
	 * @param array<string, array<string, bool>> $params Request parameters
	 * @param ZipArchiveManager $zipManager ZIP archive manager
	 */
	public function backup(array $params, ZipArchiveManager $zipManager): void {
		if (file_exists(self::CONF_PATH . 'iqrf-gateway.json')) {
			$zipManager->addFile(self::CONF_PATH . 'iqrf-gateway.json', 'gateway/iqrf-gateway.json');
		}
	}

	/**
	 * Performs gateway file restore
	 * @param ZipArchiveManager $zipManager ZIP archive manager
	 */
	public function restore(ZipArchiveManager $zipManager): void {
		if (!$zipManager->exist('gateway/')) {
			return;
		}
		if (file_exists(self::MQTT_PATH)) {
			$this->restoreLogger->log('Restoring IQRF Gateway Daemon MQTT component configuration.');
			$config = Json::decode(FileSystem::read(self::MQTT_PATH), forceArrays: true);
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
			$this->restoreLogger->log('Restoring IQRF Gateway Daemon Splitter component configuration.');
			$config = Json::decode(FileSystem::read(self::SPLITTER_PATH), forceArrays: true);
			$config['insId'] = $this->gwId;
			FileSystem::write(self::SPLITTER_PATH, Json::encode($config));
		}
	}

	/**
	 * Returns service names
	 * @return array<string> Service names
	 */
	public function getServices(): array {
		return [];
	}

}
