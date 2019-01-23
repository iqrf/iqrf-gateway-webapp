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
 * Tool for running discovery process in an IQMESH network
 */
class DiscoveryManager {

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
	 * Runs IQMESH discovery process.
	 * The time when the response is delivered depends highly on the number of network devices, the network topology, and RF mode, thus, it is not predictable. It can take from a few seconds to many minutes.
	 * @param int $txPower TX Power used for discovery.
	 * @param int $maxAddress Nonzero value specifies maximum node address to be part of the discovery process. This feature allows splitting all node devices into two parts: [1] devices having an address from 1 to MaxAddr will be part of the discovery process thus they become routers, [2] devices having an address from MaxAddr+1 to 239 will not be routers. See IQRF OS documentation for more information. The value of this parameter is ignored at demo version. A value 5 is always used instead.
	 * @return mixed[] DPA request and response
	 * @throws IqrfException\DpaErrorException
	 * @throws IqrfException\EmptyResponseException
	 * @throws IqrfException\UserErrorException
	 * @throws JsonException
	 */
	public function run(int $txPower = 6, int $maxAddress = 0): array {
		$array = [
			'mType' => 'iqrfEmbedCoordinator_Discovery',
			'data' => [
				'req' => [
					'nAdr' => 0,
					'param' => [
						'txPower' => $txPower,
						'maxAddr' => $maxAddress,
					],
				],
			],
		];
		$this->request->setRequest($array);
		return $this->wsClient->sendSync($this->request);
	}

}
