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
use Nette\SmartObject;
use Nette\Utils\Arrays;
use Nette\Utils\JsonException;

/**
 * WebSocket configuration manager
 */
class WebSocketManager {

	use SmartObject;

	/**
	 * @var string[] WebSocket components
	 */
	private $components = [
		'messaging' => 'iqrf::WebsocketMessaging',
		'service' => 'shape::WebsocketCppService',
	];

	/**
	 * @var string[] WebSocket messaging and service file names
	 */
	private $fileNames;

	/**
	 * @var GenericManager Generic configuration manager
	 */
	private $genericManager;

	/**
	 * @var array<string,string|null> WebSocket instances
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
	 * Deletes a configuration
	 * @param int $id WebSocket interface ID
	 * @throws JsonException
	 */
	public function delete(int $id): void {
		$this->genericManager->setComponent($this->components['messaging']);
		$instances = $this->genericManager->getInstanceFiles();
		$this->fileNames['messaging'] = Arrays::pick($instances, $id);
		$this->genericManager->setFileName($this->fileNames['messaging']);
		$messaging = $this->genericManager->read();
		$instance = $messaging['RequiredInterfaces'][0]['target']['instance'];
		$this->genericManager->deleteFile();
		$this->genericManager->deleteFile($this->getServiceFile($instance));
	}

	/**
	 * Gets WebSocket service file name by instance name
	 * @param string $instanceName Instance name
	 * @return string|null WebSocket service file name
	 * @throws JsonException
	 */
	public function getServiceFile(string $instanceName): ?string {
		$this->genericManager->setComponent($this->components['service']);
		$services = $this->genericManager->getInstanceFiles();
		foreach ($services as $service) {
			$this->genericManager->setFileName($service);
			$json = $this->genericManager->read();
			if (Arrays::pick($json, 'instance') === $instanceName) {
				return $service;
			}
		}
		return null;
	}

	/**
	 * Saves the configuration
	 * @param mixed[] $array Websocket settings
	 * @throws JsonException
	 */
	public function save(array $array): void {
		$timestamp = (new DateTime())->getTimestamp();
		if (!isset($this->instances['messaging'])) {
			$this->instances['messaging'] = 'WebsocketMessaging' . $timestamp;
		}
		if (!isset($this->instances['service'])) {
			$this->instances['service'] = 'WebsocketCppService' . $timestamp;
		}
		$settings = [
			'messaging' => $this->createMessaging($array),
			'service' => $this->createService($array),
		];
		foreach ($settings as $component => $config) {
			$this->schemaManager->setSchema($this->components[$component]);
			$this->schemaManager->validate((object) $config);
		}
		$messagingFileName = $this->fileNames['messaging'] ?? 'iqrf__' . $this->instances['messaging'];
		$serviceFileName = $this->fileNames['service'] ?? 'shape__' . $this->instances['service'];
		$this->genericManager->setComponent($this->components['service']);
		$this->genericManager->save($settings['service'], $serviceFileName);
		$this->genericManager->setComponent($this->components['messaging']);
		$this->genericManager->save($settings['messaging'], $messagingFileName);
	}

	/**
	 * Creates a messaging configuration
	 * @param mixed[] $values Values from form
	 * @return mixed[] Messaging configuration
	 */
	private function createMessaging(array $values): array {
		return [
			'component' => $this->components['messaging'],
			'instance' => $this->instances['messaging'],
			'acceptAsyncMsg' => $values['acceptAsyncMsg'],
			'RequiredInterfaces' => [
				(object) [
					'name' => 'shape::IWebsocketService',
					'target' => (object) [
						'instance' => $this->instances['service'],
					],
				],
			],
		];
	}

	/**
	 * Creates a service configuration
	 * @param mixed[] $values Values from the form
	 * @return mixed[] Service configuration
	 */
	private function createService(array $values): array {
		return [
			'component' => $this->components['service'],
			'instance' => $this->instances['service'],
			'WebsocketPort' => $values['port'],
			'acceptOnlyLocalhost' => $values['acceptOnlyLocalhost'],
		];
	}

	/**
	 * Gets WebSocket instances
	 * @return mixed[] WebSocket instances
	 * @throws JsonException
	 */
	public function list(): array {
		$this->genericManager->setComponent($this->components['messaging']);
		$files = $this->genericManager->getInstanceFiles();
		$instances = [];
		foreach (array_keys($files) as $id) {
			$instances[] = Arrays::mergeTree(['id' => $id], $this->load($id));
		}
		return $instances;
	}

	/**
	 * Loads a configuration
	 * @param int $id WebSocket interface ID
	 * @return mixed[] Array for form
	 * @throws JsonException
	 */
	public function load(int $id): array {
		$this->genericManager->setComponent($this->components['messaging']);
		$instances = $this->genericManager->getInstanceFiles();
		$this->fileNames['messaging'] = Arrays::pick($instances, $id);
		$this->genericManager->setFileName($this->fileNames['messaging']);
		$messaging = $this->genericManager->read();
		$serviceInstance = $messaging['RequiredInterfaces'][0]['target']['instance'];
		$this->fileNames['service'] = $this->getServiceFile($serviceInstance);
		$this->genericManager->setComponent($this->components['service']);
		$this->genericManager->setFileName($this->fileNames['service']);
		$service = $this->genericManager->read();
		$this->instances = [
			'messaging' => $messaging['instance'],
			'service' => $service['instance'],
		];
		return [
			'messagingInstance' => $messaging['instance'],
			'acceptAsyncMsg' => $messaging['acceptAsyncMsg'],
			'serviceInstance' => $service['instance'],
			'port' => $service['WebsocketPort'],
			'acceptOnlyLocalhost' => $service['acceptOnlyLocalhost'] ?? false,
		];
	}

}
