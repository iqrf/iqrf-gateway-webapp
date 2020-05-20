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

use App\IqrfNetModule\Entities\DeviceStatus;
use App\IqrfNetModule\Enums\DeviceTypes;
use App\IqrfNetModule\Exceptions\DpaErrorException;
use App\IqrfNetModule\Exceptions\EmptyResponseException;
use App\IqrfNetModule\Exceptions\UserErrorException;
use App\IqrfNetModule\Requests\ApiRequest;
use Nette\Utils\JsonException;

/**
 * Tool for showing devices in an IQMESH network
 */
class DevicesManager {

	/**
	 * @var ApiRequest JSON API request
	 */
	private $request;

	/**
	 * @var array<mixed> Table with bonded and discovered devices
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
	 * Gets table of devices
	 * @param int $base Base
	 * @param bool $ping Perform ping?
	 * @return array<mixed>|null Table of devices
	 */
	public function getTable(int $base, bool $ping = false): ?array {
		if ($base !== 10 && $base !== 16) {
			$base = 10;
		}
		$this->createEmptyTable($base);
		try {
			$this->fillTable($base, $ping);
		} catch (UserErrorException | DpaErrorException | EmptyResponseException | JsonException $e) {
			return null;
		}
		$this->table[0][0]->setType(DeviceTypes::COORDINATOR);
		return $this->table;
	}

	/**
	 * Creates an empty table for devices
	 * @param int $base Base
	 */
	private function createEmptyTable(int $base): void {
		for ($i = 0; $i < intdiv(240, $base); $i++) {
			for ($j = 0; $j < $base; $j++) {
				$this->table[$i][$j] = new DeviceStatus($i * $base + $j);
			}
		}
	}

	/**
	 * Fills table with devices
	 * @param int $base Base
	 * @param bool $ping Perform ping?
	 * @throws DpaErrorException
	 * @throws EmptyResponseException
	 * @throws JsonException
	 * @throws UserErrorException
	 */
	private function fillTable(int $base, bool $ping = false): void {
		$devices = [];
		$deviceTypes = [DeviceTypes::BONDED, DeviceTypes::DISCOVERED];
		if ($ping) {
			$deviceTypes[] = DeviceTypes::ONLINE;
		}

		foreach ($deviceTypes as $deviceType) {
			switch ($deviceType) {
				case DeviceTypes::BONDED:
					$devices = $this->getBonded();
					break;
				case DeviceTypes::DISCOVERED:
					$devices = $this->getDiscovered();
					break;
				case DeviceTypes::ONLINE:
					$devices = $this->ping();
					break;
			}
			foreach ($devices as $node) {
				if ($node > 239) {
					break;
				}
				$i = intdiv($node, $base);
				$j = $node % $base;
				$this->table[$i][$j]->setType($deviceType);
			}
		}
	}

	/**
	 * Gets bonded devices
	 * @return array<mixed> Bonded devices
	 * @throws DpaErrorException
	 * @throws EmptyResponseException
	 * @throws UserErrorException
	 * @throws JsonException
	 */
	public function getBonded(): array {
		$request = [
			'mType' => 'iqrfEmbedCoordinator_BondedDevices',
			'data' => [
				'req' => [
					'nAdr' => 0,
					'param' => (object) [],
				],
				'returnVerbose' => true,
			],
		];
		$this->request->set($request);
		$apiData = $this->wsClient->sendSync($this->request);
		return $apiData['response']->data->rsp->result->bondedDevices ?? [];
	}

	/**
	 * Gets discovered devices
	 * @return array<mixed> Discovered devices
	 * @throws DpaErrorException
	 * @throws EmptyResponseException
	 * @throws UserErrorException
	 * @throws JsonException
	 */
	public function getDiscovered(): array {
		$request = [
			'mType' => 'iqrfEmbedCoordinator_DiscoveredDevices',
			'data' => [
				'req' => [
					'nAdr' => 0,
					'param' => (object) [],
				],
				'returnVerbose' => true,
			],
		];
		$this->request->set($request);
		$apiData = $this->wsClient->sendSync($this->request);
		return $apiData['response']->data->rsp->result->discoveredDevices ?? [];
	}

	/**
	 * Perform FRC Ping
	 * @return array<mixed> Online devices
	 * @throws DpaErrorException
	 * @throws EmptyResponseException
	 * @throws JsonException
	 * @throws UserErrorException
	 */
	public function ping(): array {
		$request = [
			'mType' => 'iqrfEmbedFrc_Send',
			'data' => [
				'req' => [
					'nAdr' => 0,
					'param' => [
						'frcCommand' => 0,
						'userData' => [0, 0],
					],
					'returnVerbose' => true,
				],
			],
		];
		$this->request->set($request);
		$apiData = $this->wsClient->sendSync($this->request);
		$frcData = $apiData['response']->data->rsp->result->frcData ?? [];
		$data = [];
		foreach ($frcData as $i => $byte) {
			for ($j = 0; $j < 8; ++$j) {
				$bool = ($byte & (1 << $j)) >> $j;
				if ($bool === 1) {
					$data[] = ($i << 3) + $j;
				}
			}
		}
		return $data;
	}

}
