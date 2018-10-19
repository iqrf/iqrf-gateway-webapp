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

namespace App\IqrfAppModule\Model;

use App\IqrfAppModule\Exception as IqrfException;
use App\IqrfAppModule\Requests\ApiRequest;
use Nette\SmartObject;
use Nette\Utils\JsonException;

/**
 * Tool for controlling IQRF Gateway Daemon's mode
 */
class GwModeManager {

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
	 * Change IQRF Gateway Daemon's operation mode
	 * @param string $mode IQRF Gateway Daemon's operation mode
	 * @return array JSON API request and response
	 * @throws IqrfException\EmptyResponseException
	 * @throws IqrfException\InvalidOperationModeException
	 * @throws JsonException
	 */
	public function changeMode(string $mode): array {
		$modes = ['forwarding', 'operational', 'service'];
		if (!in_array($mode, $modes, true)) {
			throw new IqrfException\InvalidOperationModeException();
		}
		$array = [
			'mType' => 'mngDaemon_Mode',
			'data' => [
				'req' => [
					'operMode' => $mode,
				],
				'returnVerbose' => true,
			],
		];
		$this->request->setRequest($array);
		return $this->wsClient->sendSync($this->request);
	}

}
