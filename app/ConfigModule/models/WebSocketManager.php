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

use App\CoreModule\Models\JsonFileManager;
use App\CoreModule\Models\JsonSchemaManager;
use DateTime;
use Nette\SmartObject;
use Nette\Utils\Arrays;
use Nette\Utils\Json;
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
	 * @var JsonFileManager JSON file manager
	 */
	private $fileManager;

	/**
	 * @var string[] WebSocket messaging and service file names
	 */
	private $fileNames;

	/**
	 * @var GenericManager Generic configuration manager
	 */
	private $genericManager;

	/**
	 * @var string[] WebSocket instances
	 */
	private $instances;

	/**
	 * @var JsonSchemaManager JSON schema manager
	 */
	private $schemaManager;

	/**
	 * Constructor
	 * @param GenericManager $genericManager Generic configuration manager
	 * @param JsonFileManager $fileManager JSON file manager
	 * @param JsonSchemaManager $schemaManager JSON schema manager
	 */
	public function __construct(GenericManager $genericManager, JsonFileManager $fileManager, JsonSchemaManager $schemaManager) {
		$this->genericManager = $genericManager;
		$this->fileManager = $fileManager;
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
		$messaging = $this->read($this->fileNames['messaging']);
		$serviceInstance = $messaging['RequiredInterfaces'][0]['target']['instance'];
		$this->fileNames['service'] = $this->getServiceFile($serviceInstance);
		$this->fileManager->delete($this->fileNames['messaging']);
		$this->fileManager->delete($this->fileNames['service']);
	}

	/**
	 * Reads a configuration
	 * @param string $fileName File name (without .json)
	 * @return mixed[] Parsed configuration
	 * @throws JsonException
	 */
	private function read(string $fileName): array {
		$configuration = $this->fileManager->read($fileName);
		$this->genericManager->fixRequiredInterfaces($configuration);
		return $configuration;
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
			$json = $this->read($service);
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
		$instances = [
			'messaging' => $this->instances['messaging'] ?? 'WebsocketMessaging' . $timestamp,
			'service' => $this->instances['service'] ?? 'WebsocketCppService' . $timestamp,
		];
		$settings = [
			'messaging' => $this->createMessaging($array, $instances),
			'service' => $this->createService($array, $instances),
		];
		foreach ($settings as $component => $config) {
			$this->schemaManager->setSchemaFromComponent($this->components[$component]);
			$json = Json::encode($config);
			$this->schemaManager->validate(Json::decode($json));
		}
		$messagingFileName = $this->fileNames['messaging'] ?? 'iqrf__' . $instances['messaging'];
		$serviceFileName = $this->fileNames['service'] ?? 'shape__' . $instances['service'];
		$this->fileManager->write($messagingFileName, $settings['messaging']);
		$this->fileManager->write($serviceFileName, $settings['service']);
	}

	/**
	 * Creates a messaging configuration
	 * @param mixed[] $values Values from form
	 * @param string[] $instances Names of messaging and service instances
	 * @return mixed[] Messaging configuration
	 */
	public function createMessaging(array $values, array $instances): array {
		return [
			'component' => $this->components['messaging'],
			'instance' => $instances['messaging'],
			'acceptAsyncMsg' => $values['acceptAsyncMsg'],
			'RequiredInterfaces' => [
				(object) [
					'name' => 'shape::IWebsocketService',
					'target' => (object) [
						'instance' => $instances['service'],
					],
				],
			],
		];
	}

	/**
	 * Creates a service configuration
	 * @param mixed[] $values Values from form
	 * @param string[] $instances Names of messaging and service instances
	 * @return mixed[] Service configuration
	 */
	public function createService(array $values, array $instances): array {
		return [
			'component' => $this->components['service'],
			'instance' => $instances['service'],
			'WebsocketPort' => $values['port'],
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
		$messaging = $this->read($this->fileNames['messaging']);
		$serviceInstance = $messaging['RequiredInterfaces'][0]['target']['instance'];
		$this->fileNames['service'] = $this->getServiceFile($serviceInstance);
		$service = $this->read($this->fileNames['service']);
		$this->instances = [
			'messaging' => $messaging['instance'],
			'service' => $service['instance'],
		];
		$array = [
			'messagingInstance' => $messaging['instance'],
			'acceptAsyncMsg' => $messaging['acceptAsyncMsg'],
			'serviceInstance' => $service['instance'],
			'port' => $service['WebsocketPort'],
		];
		return $array;
	}

}
