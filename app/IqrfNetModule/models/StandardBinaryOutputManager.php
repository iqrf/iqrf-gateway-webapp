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

use App\IqrfNetModule\Entities\StandardBinaryOutput;
use App\IqrfNetModule\Exceptions\DpaErrorException;
use App\IqrfNetModule\Exceptions\EmptyResponseException;
use App\IqrfNetModule\Exceptions\UserErrorException;
use App\IqrfNetModule\Requests\ApiRequest;
use Nette\SmartObject;
use Nette\Utils\JsonException;

/**
 * Tool for managing standard binary output in the IQMESH network
 */
class StandardBinaryOutputManager {

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
	 * Enumerates a IQRF Standard binary output device
	 * @param int $address Network device address
	 * @return mixed[] API request and response
	 * @throws DpaErrorException
	 * @throws EmptyResponseException
	 * @throws JsonException
	 * @throws UserErrorException
	 */
	public function enumerate(int $address): array {
		$array = [
			'mType' => 'iqrfBinaryoutput_Enumerate',
			'data' => [
				'req' => [
					'nAdr' => $address,
					'param' => (object) [],
				],
				'returnVerbose' => true,
			],
		];
		$this->request->setRequest($array);
		return $this->wsClient->sendSync($this->request);
	}

	/**
	 * Gets states of IQRF Standard binary outputs
	 * @param int $address Network device address
	 * @return mixed[] API request and response
	 * @throws DpaErrorException
	 * @throws EmptyResponseException
	 * @throws JsonException
	 * @throws UserErrorException
	 */
	public function getOutputs(int $address): array {
		return $this->setOutputs($address, []);
	}

	/**
	 * Sets states of IQRF Standard binary outputs
	 * @param int $address Network device address
	 * @param StandardBinaryOutput[] $outputs Standard binary output
	 * @return mixed[] API request and response
	 * @throws DpaErrorException
	 * @throws EmptyResponseException
	 * @throws JsonException
	 * @throws UserErrorException
	 */
	public function setOutputs(int $address, array $outputs): array {
		$array = [
			'mType' => 'iqrfBinaryoutput_SetOutput',
			'data' => [
				'req' => [
					'nAdr' => $address,
					'param' => [
						'binouts' => [],
					],
				],
				'returnVerbose' => true,
			],
		];
		/**
		 * @var StandardBinaryOutput $output Standard binary output
		 */
		foreach ($outputs as $output) {
			$array['data']['req']['param']['binouts'][] = $output->toArray();
		}
		$this->request->setRequest($array);
		return $this->wsClient->sendSync($this->request);
	}

}
