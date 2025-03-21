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

namespace App\GatewayModule\Models\Backup;

use App\CoreModule\Models\ZipArchiveManager;
use App\GatewayModule\Models\Utils\GatewayInfoUtil;
use Iqrf\FileManager\PrivilegedFileManager;
use Nette\Utils\Strings;

/**
 * Gateway file backup manager
 */
class GatewayFileBackup implements IBackupManager {

	/**
	 * Gateway files whitelist
	 */
	final public const WHITELIST = [
		'iqrf-gateway.json',
	];

	/**
	 * Path to configuration directory
	 */
	private const CONF_PATH = '/etc/';

	/**
	 * Path to MqttMessaging component configuration
	 */
	private const MQTT_FILE = 'iqrf__MqttMessaging.json';

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
	private const SPLITTER_FILE = 'iqrf__JsonSplitter.json';

	/**
	 * @var string Gateway ID
	 */
	private readonly string $gwId;

	/**
	 * Constructor
	 * @param PrivilegedFileManager $daemonFileManager Daemon file manager
	 * @param GatewayInfoUtil $gwInfo Gateway information utility
	 */
	public function __construct(
		private readonly PrivilegedFileManager $daemonFileManager,
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
		if ($this->daemonFileManager->exists(self::MQTT_FILE)) {
			$this->restoreLogger->log('Restoring IQRF Gateway Daemon MQTT component configuration.');
			$config = $this->daemonFileManager->readJson(self::MQTT_FILE);
			if (Strings::match($config['ClientId'], self::GWID_PATTERN) !== null) {
				$config['ClientId'] = $this->gwId;
			}
			if (Strings::match($config['TopicRequest'], self::REQUEST_TOPIC_PATTERN) !== null) {
				$config['TopicRequest'] = 'gateway/' . $this->gwId . '/iqrf/requests';
			}
			if (Strings::match($config['TopicResponse'], self::RESPONSE_TOPIC_PATTERN) !== null) {
				$config['TopicResponse'] = 'gateway/' . $this->gwId . '/iqrf/responses';
			}
			$this->daemonFileManager->writeJson(self::MQTT_FILE, $config);
		}
		if ($this->daemonFileManager->exists(self::SPLITTER_FILE)) {
			$this->restoreLogger->log('Restoring IQRF Gateway Daemon Splitter component configuration.');
			$config = $this->daemonFileManager->readJson(self::SPLITTER_FILE);
			$config['insId'] = $this->gwId;
			$this->daemonFileManager->writeJson(self::SPLITTER_FILE, $config);
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
