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

class ConfigParser {

	use Nette\SmartObject;

	/**
	 * Convert array from Components configuration form to JSON
	 * @param array $components Components configuration form array
	 * @return array JSON array
	 */
	public function componentsToJson($components) {
		$array = [];
		foreach ($components as $component => $enabled) {
			array_push($array, ['ComponentName' => $component, 'Enabled' => $enabled]);
		}
		return $array;
	}

	/**
	 * Convert array from Base Service configuration form to JSON
	 * @param array $services Base Service JSON array
	 * @param ArrayHash $update Changed settings
	 * @param int $id Base Service ID
	 * @return array JSON array
	 */
	public function baseServiceToJson(array $services, ArrayHash $update, $id) {
		$service = [];
		$service['Name'] = $update['Name'];
		$service['Messaging'] = $update['Messaging'];
		$service['Serializers'] = array_values((array) $update['Serializers']);
		if (isset($update['Properties'])) {
			$service['Properties'] = (array) $update['Properties'];
		} else {
			$service['Properties'] = [];
		}

		$services['Instances'][$id] = $service;
		return $services;
	}

	/**
	 * Convert JSON array to Base Service configuration form
	 * @param array $json Base Service JSON array
	 * @param int $id Base Service ID
	 * @return array Array for form
	 */
	public function baseServiceToForm(array $json, $id = 0) {
		$data = $json['Instances'][$id];
		$service = [];
		$service['Name'] = $data['Name'];
		$service['Messaging'] = $data['Messaging'];
		$service['Serializers'] = (array) $data['Serializers'];
		return $service;
	}

	/**
	 * Convert array from Interfaces configuration form to JSON
	 * @param array $instances Original Instances JSON array
	 * @param ArrayHash $update Changed settings
	 * @param int $id Interface ID
	 * @return array JSON array
	 */
	public function instancesToJson(array $instances, ArrayHash $update, $id) {
		$instance = [];
		$instance['Name'] = $update['Name'];
		$instance['Enabled'] = $update['Enabled'];
		unset($update['Name']);
		unset($update['Enabled']);
		$instance['Properties'] = (array) $update;
		$instances[$id] = $instance;
		return $instances;
	}

	/**
	 * Convert Instances configuration form array to JSON array
	 * @param array $json Interfaces JSON array
	 * @param int $id Interface ID
	 * @return array Array for form
	 */
	public function instancesToForm(array $json, $id = 0) {
		$data = $json['Instances'][$id];
		$instance = $data['Properties'];
		$instance['Name'] = $data['Name'];
		$instance['Enabled'] = $data['Enabled'];
		return $instance;
	}

	/**
	 * Convert Scheduler configuration form array to JSON array
	 * @param array $scheduler Original Scheduler JSON array
	 * @param ArrayHash $update Changed settings
	 * @param int $id Scheduler ID
	 * @return array JSON array
	 */
	public function schedulerToJson(array $scheduler, ArrayHash $update, $id) {
		$data = [];
		$data['time'] = $update['time'];
		$data['service'] = $update['service'];
		unset($update['service']);
		unset($update['time']);
		if (array_key_exists('sensors', $update)) {
			$sensors = explode(PHP_EOL, $update['sensors']);
			unset($update['sensors']);
			$update['sensors'] = $sensors;
		}
		$data['message'] = (array) $update;
		$scheduler['TasksJson'][$id] = $data;
		return $scheduler;
	}

	/**
	 * Convert Scheduler JSON array to Scheduler configuration form array
	 * @param array $json Scheduler JSON array
	 * @param int $id Scheduler ID
	 * @return array Array for form
	 */
	public function schedulerToForm(array $json, $id = 0) {
		$data = $json['TasksJson'][$id];
		$scheduler = [];
		$scheduler['time'] = $data['time'];
		$scheduler['service'] = $data['service'];
		if (array_key_exists('sensors', $data['message'])) {
			$sensors = implode(PHP_EOL, $data['message']['sensors']);
			unset($scheduler['message']['sensors']);
			$scheduler += $data['message'];
			$scheduler['sensors'] = $sensors;
		} else {
			$scheduler += $data['message'];
		}
		return $scheduler;
	}

}
