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

namespace App\ConfigModule\Models;

use DateTime;
use Nette\Utils\Arrays;
use Nette\Utils\JsonException;
use stdClass;

/**
 * IQRF Gateway Daemon Monitor service manager
 */
class MonitorManager {

	/**
	 * @var array<string,string> Components
	 */
	private $components = [
		'monitor' => 'iqrf::MonitorService',
		'webSocket' => 'shape::WebsocketCppService',
	];

	/**
	 * @var array<string> JSON file names
	 */
	private $fileNames = [];

	/**
	 * @var GenericManager Generic configuration manager
	 */
	private $genericManager;

	/**
	 * @var array<string,string|null> Component instances
	 */
	private $instances = [];

	/**
	 * @var ComponentSchemaManager JSON schema manager
	 */
	private $schemaManager;

	/**
	 * Constructor
	 * @param GenericManager $genericManager Generic configuration manager
	 * @param ComponentSchemaManager $schemaManager JSON schema manager
	 */
	public function __construct(GenericManager $genericManager, ComponentSchemaManager $schemaManager) {
		$this->genericManager = $genericManager;
		$this->schemaManager = $schemaManager;
	}

	/**
	 * Deletes Daemon's monitor service configuration
	 * @param int $id Daemon's monitor service instance ID
	 * @throws JsonException
	 */
	public function delete(int $id): void {
		$this->genericManager->setComponent($this->components['monitor']);
		$instances = $this->genericManager->getInstanceFiles();
		$this->fileNames['monitor'] = Arrays::pick($instances, $id);
		$messaging = $this->genericManager->read($this->fileNames['monitor']);
		$instance = $messaging['RequiredInterfaces'][0]['target']['instance'];
		$this->genericManager->deleteFile($this->fileNames['monitor']);
		$this->genericManager->deleteFile($this->getWebSocketFile($instance));
	}

	/**
	 * Returns Websocket service file name by instance name
	 * @param string $instance Websocket service instance name
	 * @return string|null WebSocket service file name
	 * @throws JsonException
	 */
	private function getWebSocketFile(string $instance): ?string {
		$this->genericManager->setComponent($this->components['webSocket']);
		$services = $this->genericManager->getInstanceFiles();
		foreach ($services as $service) {
			$json = $this->genericManager->read($service);
			if (Arrays::pick($json, 'instance') === $instance) {
				return $service;
			}
		}
		return null;
	}

	/**
	 * Lists Daemon's monitor instances
	 * @return array<int, array<string, bool|int|string>> Daemon's monitor instances
	 * @throws JsonException
	 */
	public function list(): array {
		$this->genericManager->setComponent($this->components['monitor']);
		$files = $this->genericManager->getInstanceFiles();
		$instances = [];
		foreach (array_keys($files) as $id) {
			$instances[] = Arrays::mergeTree(['id' => $id], $this->load($id));
		}
		return $instances;
	}

	/**
	 * Loads Daemon's monitor service configuration
	 * @param int $id Daemon's monitor service ID
	 * @return array<string, bool|int|string> Array for the configuration form
	 * @throws JsonException
	 */
	public function load(int $id): array {
		$this->genericManager->setComponent($this->components['monitor']);
		$instances = $this->genericManager->getInstanceFiles();
		$this->fileNames['monitor'] = Arrays::pick($instances, $id);
		$monitor = $this->genericManager->read($this->fileNames['monitor']);
		$webSocketInstance = $monitor['RequiredInterfaces'][0]['target']['instance'];
		$this->fileNames['webSocket'] = $this->getWebSocketFile($webSocketInstance);
		$this->genericManager->setComponent($this->components['webSocket']);
		$webSocket = $this->genericManager->read($this->fileNames['webSocket']);
		$this->instances = [
			'monitor' => $monitor['instance'],
			'webSocket' => $webSocket['instance'],
		];
		return [
			'monitorInstance' => $this->instances['monitor'],
			'reportPeriod' => $monitor['reportPeriod'],
			'webSocketInstance' => $this->instances['webSocket'],
			'port' => $webSocket['WebsocketPort'],
			'acceptOnlyLocalhost' => $webSocket['acceptOnlyLocalhost'] ?? false,
		];
	}

	/**
	 * Saves Daemon's monitor service configuration
	 * @param array<string, bool|int|string> $array Daemon's monitor service configuration
	 * @throws JsonException
	 */
	public function save(array $array): void {
		$timestamp = (new DateTime())->getTimestamp();
		if (!isset($this->instances['monitor'])) {
			$this->instances['monitor'] = 'MonitorService' . $timestamp;
		}
		if (!isset($this->instances['webSocket'])) {
			$this->instances['webSocket'] = 'WebsocketCppService_Monitor' . $timestamp;
		}
		$configuration = [
			'monitor' => $this->createMonitorService($array),
			'webSocket' => $this->createWebSocketService($array),
		];
		foreach ($configuration as $component => $config) {
			$this->schemaManager->setSchema($this->components[$component]);
			$this->schemaManager->validate((object) $config);
		}
		$this->fileNames['monitor'] = $this->fileNames['monitor'] ?? 'iqrf__' . $this->instances['monitor'];
		$this->fileNames['webSocket'] = $this->fileNames['webSocket'] ?? 'shape__' . $this->instances['webSocket'];
		$this->genericManager->setComponent($this->components['webSocket']);
		$this->genericManager->save($configuration['webSocket'], $this->fileNames['webSocket']);
		$this->genericManager->setComponent($this->components['monitor']);
		$this->genericManager->save($configuration['monitor'], $this->fileNames['monitor']);
	}

	/**
	 * Creates a Monitor service configuration
	 * @param array<string, bool|int|string> $values Values from the form
	 * @return array<string, array<int, stdClass>|bool|string> Monitor service configuration
	 */
	private function createMonitorService(array $values): array {
		return [
			'component' => $this->components['monitor'],
			'instance' => $this->instances['monitor'],
			'reportPeriod' => $values['reportPeriod'],
			'RequiredInterfaces' => [
				(object) [
					'name' => 'shape::IWebsocketService',
					'target' => (object) [
						'instance' => $this->instances['webSocket'],
					],
				],
			],
		];
	}

	/**
	 * Creates a WebSocket service configuration
	 * @param array<string, bool|int|string> $values Values from the form
	 * @return array<string, bool|int|string> WebSocket service configuration
	 */
	private function createWebSocketService(array $values): array {
		return [
			'component' => $this->components['webSocket'],
			'instance' => $this->instances['webSocket'],
			'WebsocketPort' => $values['port'],
			'acceptOnlyLocalhost' => $values['acceptOnlyLocalhost'],
		];
	}

}
