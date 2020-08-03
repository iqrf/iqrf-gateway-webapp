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

use App\IqrfNetModule\Exceptions\DpaErrorException;
use App\IqrfNetModule\Exceptions\EmptyResponseException;
use App\IqrfNetModule\Exceptions\UserErrorException;
use App\IqrfNetModule\Requests\ApiRequest;
use Nette\Utils\JsonException;

/**
 * Tool for managing bonds in an IQMESH network
 */
class BondingManager {

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
	 * Bonds a node locally
	 * @param int $address A requested address for the bonded node. If this parameter equals to 0, then the first free address is assigned to the node.
	 * @return array<mixed> API request and response
	 * @throws DpaErrorException
	 * @throws EmptyResponseException
	 * @throws UserErrorException
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
		$this->request->set($array);
		return $this->wsClient->sendSync($this->request);
	}

	/**
	 * Bonds a node via IQRF Smart Connect
	 * @param int $address Address to bond the device to
	 * @param string $code Smart connect code of the device
	 * @param int $testRetries Maximum number of FRCs used to test whether the Node was successfully bonded.
	 * @return array<mixed> API request and response
	 * @throws DpaErrorException
	 * @throws EmptyResponseException
	 * @throws UserErrorException
	 * @throws JsonException
	 */
	public function bondSmartConnect(int $address, string $code, int $testRetries = 1): array {
		$array = [
			'mType' => 'iqmeshNetwork_SmartConnect',
			'data' => [
				'repeat' => 2,
				'req' => [
					'deviceAddr' => $address,
					'smartConnectCode' => $code,
					'bondingTestRetries' => $testRetries,
				],
				'returnVerbose' => true,
			],
		];
		$this->request->set($array);
		return $this->wsClient->sendSync($this->request);
	}

	/**
	 * Clears all bonds
	 * @param bool $coordinatorOnly Removes a bond only in the coordinator
	 * @return array<mixed> API request and response
	 * @throws DpaErrorException
	 * @throws EmptyResponseException
	 * @throws UserErrorException
	 * @throws JsonException
	 */
	public function clearAll(bool $coordinatorOnly = false): array {
		if ($coordinatorOnly) {
			$array = [
				'mType' => 'iqrfEmbedCoordinator_ClearAllBonds',
				'data' => [
					'req' => [
						'nAdr' => 0,
						'param' => (object) [],
					],
					'returnVerbose' => true,
				],
			];
			$this->request->set($array);
			return $this->wsClient->sendSync($this->request);
		}
		return $this->remove(255, false);
	}

	/**
	 * Removes a bond
	 * @param int $address Address of the node to be removed
	 * @param bool $coordinatorOnly Removes a bond only in the coordinator
	 * @return array<mixed> API request and response
	 * @throws DpaErrorException
	 * @throws EmptyResponseException
	 * @throws UserErrorException
	 * @throws JsonException
	 */
	public function remove(int $address, bool $coordinatorOnly): array {
		if ($coordinatorOnly) {
			$array = [
				'mType' => 'iqrfEmbedCoordinator_RemoveBond',
				'data' => [
					'req' => [
						'nAdr' => 0,
						'param' => [
							'bondAddr' => $address,
						],
					],
					'returnVerbose' => true,
				],
			];
		} else {
			$array = [
				'mType' => 'iqmeshNetwork_RemoveBond',
				'data' => [
					'repeat' => 2,
					'req' => [
						'deviceAddr' => $address,
					],
					'returnVerbose' => true,
				],
			];
		}
		$this->request->set($array);
		return $this->wsClient->sendSync($this->request);
	}

}
