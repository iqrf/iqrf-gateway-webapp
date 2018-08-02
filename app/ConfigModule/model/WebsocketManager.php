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

use App\Model\JsonFileManager;
use App\Model\JsonSchemaManager;
use Nette;
use Nette\Utils\Arrays;
use Nette\Utils\ArrayHash;
use Nette\Utils\Finder;
use Nette\Utils\Strings;

class WebsocketManager {

	use Nette\SmartObject;

	/**
	 * @var array Websocket components
	 */
	private $components = [
		'messaging' => 'iqrf::WebsocketMessaging',
		'service' => 'shape::WebsocketService',
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
	 */
	public function delete(int $id) {
		$instances = $this->getInstanceFiles($this->components['messaging']);
		$this->fileNames['messaging'] = Arrays::pick($instances, $id);
		$messaging = $this->read($this->fileNames['messaging']);
		$serviceInsatnce = $messaging['RequiredInterfaces'][0]['target']['instance'];
		$this->fileNames['service'] = $this->getServiceFile($serviceInsatnce);
		$this->fileManager->delete($this->fileNames['messaging']);
		$this->fileManager->delete($this->fileNames['service']);
	}

	/**
	 * Load a configuration
	 * @return array Array for form
	 */
	public function load(int $id): array {
		$instances = $this->getInstanceFiles($this->components['messaging']);
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
		return $this->genericManager->fixRequiredInterfaces($configuration);
	}

	/**
	 * Save configuration
	 * @param ArrayHash $array Websocket settings
	 */
	public function save(ArrayHash $array) {
		$settings = [];
		$now = new \DateTime();
		$messagingInstance = isset($this->instances['messaging']) ? $this->instances['messaging'] : 'WebsocketMessaging' . $now->getTimestamp();
		$serviceInstance = isset($this->instances['services']) ? $this->instances['service'] : 'WebsocketService' . $now->getTimestamp();
		$settings['messaging'] = [
			'component' => $this->components['messaging'],
			'instance' => $messagingInstance,
			'acceptAsyncMsg' => $array['acceptAsyncMsg'],
			'RequiredInterfaces' => [
				(object) [
					'name' => 'shape::IWebsocketService',
					'target' => (object) [
						'instance' => $serviceInstance,
					],
				],
			],
		];
		$settings['service'] = [
			'component' => $this->components['service'],
			'instance' => $serviceInstance,
			'WebsocketPort' => $array['port'],
		];
		foreach ($settings as $component => $config) {
			$this->schemaManager->setSchemaFromComponent($this->components[$component]);
			$this->schemaManager->validate((object) $config);
		}
		$messagingFileName = isset($this->fileNames['messaging']) ? $this->fileNames['messaging'] : 'iqrf__' . $messagingInstance;
		$serviceFileName = isset($this->fileNames['services']) ? $this->fileNames['service'] : 'shape__' . $serviceInstance;
		$this->fileManager->write($messagingFileName, ArrayHash::from($settings['messaging']));
		$this->fileManager->write($serviceFileName, ArrayHash::from($settings['service']));
	}

	/**
	 * Get component's instance
	 * @return array Component's instances
	 */
	public function getInstances(): array {
		$files = $this->getInstanceFiles($this->components['messaging']);
		$instances = [];
		foreach ($files as $id => $file) {
			$instances[] = $this->load($id);
		}
		return $instances;
	}

	/**
	 * Get component's instance files
	 * @param string $component Components name
	 * @return array Files with component's instances
	 */
	public function getInstanceFiles(string $component): array {
		$dir = $this->fileManager->getDirectory();
		$instances = [];
		foreach (Finder::findFiles('*.json')->exclude('config.json')->from($dir) as $file) {
			$fileName = Strings::replace($file->getRealPath(), ['~^' . realpath($dir) . '/~', '/.json$/'], '');
			$json = $this->read($fileName);
			if (Arrays::pick($json, 'component', null) === $component) {
				$instances[] = $fileName;
			}
		}
		return $instances;
	}

	/**
	 * Get websocket service file name by instance name
	 * @param string $instanceName Instance name
	 * @return string|null Websocket service file name
	 */
	public function getServiceFile(string $instanceName) {
		$services = $this->getInstanceFiles($this->components['service']);
		foreach ($services as $service) {
			$json = $this->read($service);
			if (Arrays::pick($json, 'instance') === $instanceName) {
				return $service;
			}
		}
	}

}
