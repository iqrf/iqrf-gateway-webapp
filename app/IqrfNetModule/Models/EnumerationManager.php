<?php

/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
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
use App\IqrfNetModule\Requests\ApiRequest;
use Nette\Utils\JsonException;

/**
 * Tool for enumerate device in the IQMESH network
 */
class EnumerationManager {

	/**
	 * @var ApiRequest JSON API request
	 */
	private ApiRequest $request;

	/**
	 * @var WebSocketClient WebSocket client
	 */
	private WebSocketClient $wsClient;

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
	 * Enumerates a device
	 * @param int $address Device address to enumerate
	 * @return array<mixed> API request and response
	 * @throws DpaErrorException
	 * @throws EmptyResponseException
	 * @throws JsonException
	 */
	public function device(int $address): array {
		$array = [
			'mType' => 'iqmeshNetwork_EnumerateDevice',
			'data' => [
				'repeat' => 2,
				'req' => [
					'deviceAddr' => $address,
					'morePeripheralsInfo' => true,
				],
				'returnVerbose' => true,
			],
		];
		$this->request->set($array);
		return $this->wsClient->sendSync($this->request);
	}

}
