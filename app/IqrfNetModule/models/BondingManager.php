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

namespace App\IqrfNetModule\Models;

use App\IqrfNetModule\Exceptions as IqrfException;
use App\IqrfNetModule\Requests\ApiRequest;
use Nette\SmartObject;
use Nette\Utils\JsonException;

/**
 * Tool for managing bonds in an IQMESH network
 */
class BondingManager {

	use SmartObject;

	/**
	 * @var ApiRequest JSON API request
	 */
	private $request;

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
	 * Local bond a node
	 * @param int $address A requested address for the bonded node. If this parameter equals to 0, then the first free address is assigned to the node.
	 * @return mixed[] DPA request and response
	 * @throws IqrfException\EmptyResponseException
	 * @throws JsonException
	 */
	public function bondLocal(int $address = 0): array {
		$array = [
			'mType' => 'iqmeshNetwork_BondNodeLocal',
			'data' => [
				'repeat' => 2,
				'req' => [
					'deviceAddr' => $address,
				],
				'returnVerbose' => true,
			],
		];
		$this->request->setRequest($array);
		return $this->wsClient->sendSync($this->request);
	}

	/**
	 * Bond a node via IQRF Smart Connect
	 * @param int $address Address to bond the device to.
	 * @param string $code Smart connect code of the device.
	 * @return mixed[] DPA request and response
	 * @throws IqrfException\EmptyResponseException
	 * @throws JsonException
	 */
	public function bondSmartConnect(int $address, string $code): array {
		$array = [
			'mType' => 'iqmeshNetwork_SmartConnect',
			'data' => [
				'repeat' => 2,
				'req' => [
					'deviceAddr' => $address,
					'smartConnectCode' => $code,
				],
				'returnVerbose' => true,
			],
		];
		$this->request->setRequest($array);
		return $this->wsClient->sendSync($this->request);
	}

	/**
	 * Clear all bonds
	 * @return mixed[] DPA request and response
	 * @throws IqrfException\EmptyResponseException
	 * @throws JsonException
	 */
	public function clearAll(): array {
		$array = [
			'mType' => 'iqrfEmbedCoordinator_ClearAllBonds',
			'data' => [
				'req' => [
					'nAdr' => 0,
				],
				'returnVerbose' => true,
			],
		];
		$this->request->setRequest($array);
		return $this->wsClient->sendSync($this->request);
	}

	/**
	 * Re-bond a node
	 * @param int $address Address of the node to be re-bonded.
	 * @return mixed[] DPA request and response
	 * @throws IqrfException\EmptyResponseException
	 * @throws JsonException
	 */
	public function rebond(int $address): array {
		$array = [
			'mType' => 'iqrfEmbedCoordinator_RebondNode',
			'data' => [
				'req' => [
					'nAdr' => 0,
					'param' => [
						'bondAddr' => $address,
					],
				],
			],
		];
		$this->request->setRequest($array);
		return $this->wsClient->sendSync($this->request);
	}

	/**
	 * Remove a bond
	 * @param int $address Address of the node to be removed.
	 * @return mixed[] DPA request and response
	 * @throws IqrfException\EmptyResponseException
	 * @throws JsonException
	 */
	public function remove(int $address): array {
		$array = [
			'mType' => 'iqrfEmbedCoordinator_RemoveBond',
			'data' => [
				'req' => [
					'nAdr' => 0,
					'bondAddr' => $address,
				],
				'returnVerbose' => true,
			],
		];
		$this->request->setRequest($array);
		return $this->wsClient->sendSync($this->request);
	}

}
