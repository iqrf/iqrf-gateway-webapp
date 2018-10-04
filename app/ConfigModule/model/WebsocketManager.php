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

namespace App\ConfigModule\Model;

use App\CoreModule\Model\JsonFileManager;
use App\CoreModule\Model\JsonSchemaManager;
use Nette;
use Nette\Utils\Arrays;
use Nette\Utils\Json;

/**
 * Websocket configuration manager
 */
class WebsocketManager {

	use Nette\SmartObject;

	/**
	 * @var array Websocket components
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
	 * @var array Websocket messaging and service file names
	 */
	private $fileNames;

	/**
	 * @var GenericManager Generic configuration manager
	 */
	private $genericManager;

	/**
	 * @var array Websocket instances
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
	 * Delete a configuration
	 * @param int $id Websocket interface ID
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
	 * Load a configuration
	 * @param int $id Websocket interface ID
	 * @return array Array for form
	 */
	public function load(int $id): array {
		$this->genericManager->setComponent($this->components['messaging']);
		$instances = $this->genericManager->getInstanceFiles();
		$this->fileNames['messaging'] = Arrays::pick($instances, $id);
		$messaging = $this->read($this->fileNames['messaging']);
		$serviceInsatnce = $messaging['RequiredInterfaces'][0]['target']['instance'];
		$this->fileNames['service'] = $this->getServiceFile($serviceInsatnce);
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

	/**
	 * Read a configuration
	 * @param string $fileName File name (without .json)
	 * @return array Parsed configuration
	 */
	private function read(string $fileName): array {
		$configuration = $this->fileManager->read($fileName);
		$this->genericManager->fixRequiredInterfaces($configuration);
		return $configuration;
	}

	/**
	 * Save configuration
	 * @param array $array Websocket settings
	 */
	public function save(array $array): void {
		$timestamp = (new \DateTime())->getTimestamp();
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
	 * Get websocket instances
	 * @return array Websocket instances
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
	 * Create a messaging configuration
	 * @param array $values Values from form
	 * @param array $instances Names of messaging and service instances
	 * @return array Messaging configuration
	 */
	public function createMessaging(array $values, array $instances): array {
		return [
			'component' => $this->components['messaging'],
			'instance' => $instances['messaging'],
			'acceptAsyncMsg' => $values['acceptAsyncMsg'],
			'RequiredInterfaces' => [
				(object)[
					'name' => 'shape::IWebsocketService',
					'target' => (object)[
						'instance' => $instances['service'],
					],
				],
			],
		];
	}

	/**
	 * Create a service configuration
	 * @param array $values Values from form
	 * @param array $instances Names of messaging and service instances
	 * @return array Service configuration
	 */
	public function createService(array $values, array $instances): array {
		return [
			'component' => $this->components['service'],
			'instance' => $instances['service'],
			'WebsocketPort' => $values['port'],
		];
	}

	/**
	 * Get websocket service file name by instance name
	 * @param string $instanceName Instance name
	 * @return string|null Websocket service file name
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

}
