<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace App\Model;

use Nette;
use Nette\Utils\ArrayHash;
use Nette\Utils\Finder;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;

class ConfigManager {

	use Nette\SmartObject;

	/**
	 * @var string
	 * @inject
	 */
	private $configDir;

	/**
	 * Constructor
	 * @param string $configDir Directory with configuration files
	 */
	public function __construct($configDir) {
		$this->configDir = $configDir;
	}

	public function read($name) {
		$json = FileSystem::read($this->configDir . '/' . $name . '.json');
		return Json::decode($json, Json::FORCE_ARRAY);
	}

	public function getAll() {
		$fileNames = [];
		foreach (Finder::findFiles('*.json')->in($this->configDir) as $path => $file) {
			array_push($fileNames, str_replace($this->configDir . '/', '', $path));
			echo $path; // $path je řetězec s názvem souboru včetně cesty
			echo $file; // $file je objektem SplFileInfo
		}
		return $fileNames;
	}

	public function parseComponents($components) {
		$array = [];
		foreach ($components as $component => $enabled) {
			array_push($array, ['ComponentName' => $component, 'Enabled' => $enabled]);
		}
		return $array;
	}

	/**
	 * Parse interfaces
	 * @param array $interfaces
	 * @param ArrayHash $update
	 * @param int $id
	 * @return array
	 */
	public function parseInstances(array $interfaces, ArrayHash $update, $id) {
		$interface = [];
		$interface['Name'] = $update['Name'];
		unset($update['Name']);
		$interface['Enabled'] = $update['Enabled'];
		unset($update['Enabled']);
		$interface['Properties'] = (array) $update;
		$interfaces[$id] = $interface;
		return $interfaces;
	}

	public function saveComponents($components) {
		$file = $this->configDir . '/config.json';
		$json = Json::decode(FileSystem::read($file), Json::FORCE_ARRAY);
		$json['Components'] = $this->parseComponents($components);
		FileSystem::write($file, Json::encode($json, Json::PRETTY));
	}

	public function saveMqtt($mqtt, $id) {
		$file = $this->configDir . '/MqttMessaging.json';
		$json = Json::decode(FileSystem::read($file), Json::FORCE_ARRAY);
		$json['Instances'] = $this->parseInstances($json['Instances'], $mqtt, $id);
		FileSystem::write($file, Json::encode($json, Json::PRETTY));
	}

	public function saveUdp($udp) {
		$file = $this->configDir . '/UdpMessaging.json';
		$json = Json::decode(FileSystem::read($file), Json::FORCE_ARRAY);
		$json['Instances'] = $this->parseInstances($json['Instances'], $udp, 0);
		FileSystem::write($file, Json::encode($json, Json::PRETTY));
	}

}
