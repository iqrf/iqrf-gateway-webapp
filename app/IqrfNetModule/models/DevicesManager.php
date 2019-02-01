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

namespace App\IqrfNetModule\Models;

use App\IqrfNetModule\Enums\DeviceTypes;
use App\IqrfNetModule\Exceptions as IqrfException;
use App\IqrfNetModule\Requests\ApiRequest;
use Nette\SmartObject;
use Nette\Utils\JsonException;

/**
 * Tool for showing devices in an IQMESH network
 */
class DevicesManager {

	use SmartObject;

	/**
	 * @var ApiRequest JSON API request
	 */
	private $request;

	/**
	 * @var mixed[] Table with bonded and discovered devices
	 */
	private $table;

	/**
	 * @var WebSocketClient WebSocket client
	 */
	private $wsClient;

	/**
	 * Constructor
	 * @param ApiRequest $request JSON API request
	 * @param WebSocketClient $wsClient WebSocket client
	 */
	public function __construct(ApiRequest $request, WebSocketClient $wsClient) {
		$this->request = $request;
		$this->wsClient = $wsClient;
	}

	/**
	 * Get bonded devices
	 * @return mixed[] DPA request and response
	 * @throws IqrfException\DpaErrorException
	 * @throws IqrfException\EmptyResponseException
	 * @throws IqrfException\UserErrorException
	 * @throws JsonException
	 */
	public function getBonded(): array {
		$array = [
			'mType' => 'iqrfEmbedCoordinator_BondedDevices',
			'data' => [
				'req' => [
					'nAdr' => 0,
					'param' => (object) [],
				],
				'returnVerbose' => true,
			],
		];
		$this->request->setRequest($array);
		return $this->wsClient->sendSync($this->request);
	}

	/**
	 * Get discovered devices
	 * @return mixed[] DPA request and response
	 * @throws IqrfException\DpaErrorException
	 * @throws IqrfException\EmptyResponseException
	 * @throws IqrfException\UserErrorException
	 * @throws JsonException
	 */
	public function getDiscovered(): array {
		$array = [
			'mType' => 'iqrfEmbedCoordinator_DiscoveredDevices',
			'data' => [
				'req' => [
					'nAdr' => 0,
					'param' => (object) [],
				],
				'returnVerbose' => true,
			],
		];
		$this->request->setRequest($array);
		return $this->wsClient->sendSync($this->request);
	}

	/**
	 * Create an empty table for bonded and discovered devices
	 * @param int $base Base
	 */
	private function createEmptyTable(int $base): void {
		for ($i = 0; $i < (240 / $base); $i++) {
			for ($j = 0; $j < $base; $j++) {
				$this->table[$i][$j] = DeviceTypes::NONE;
			}
		}
	}

	/**
	 * Fill table with bonded or discovered devices
	 * @param int $deviceType Bonded or discovered devices
	 * @param int $base Base
	 */
	private function fillTable(int $deviceType, int $base): void {
		switch ($deviceType) {
			case DeviceTypes::BONDED:
				try {
					$devices = $this->getBonded()['response']['data']['rsp']['result']['bondedDevices'] ?? [];
				} catch (IqrfException\UserErrorException | IqrfException\DpaErrorException | IqrfException\EmptyResponseException | JsonException $e) {
					$devices = [];
				}
				break;
			case DeviceTypes::DISCOVERED:
				try {
					$devices = $this->getDiscovered()['response']['data']['rsp']['result']['discoveredDevices'] ?? [];
				} catch (IqrfException\UserErrorException | IqrfException\DpaErrorException | IqrfException\EmptyResponseException | JsonException $e) {
					$devices = [];
				}
				break;
			default:
				$devices = [];
		}
		foreach ($devices as $node) {
			$this->table[intdiv($node, $base)][$node % $base] = $deviceType;
		}
	}

	/**
	 * Get table of bonded and discovered devices
	 * @param int $base Base
	 * @return mixed[]|null Table of bonded and discovered devices
	 */
	public function getTable(int $base): ?array {
		if ($base !== 10 && $base !== 16) {
			$base = 10;
		}
		$this->createEmptyTable($base);
		$this->fillTable(DeviceTypes::BONDED, $base);
		$this->fillTable(DeviceTypes::DISCOVERED, $base);
		return $this->table;
	}

}
