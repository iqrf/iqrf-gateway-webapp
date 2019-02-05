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
use Nette\SmartObject;
use Nette\Utils\JsonException;

/**
 * Tool for managing standard lights in the IQMESH network
 */
class StandardLightManager {

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
	 * Enumerate device
	 * @param int $address Network device address
	 * @return mixed[] DPA request and response
	 * @throws DpaErrorException
	 * @throws EmptyResponseException
	 * @throws UserErrorException
	 * @throws JsonException
	 */
	public function enumerate(int $address): array {
		$array = [
			'mType' => 'iqrfLight_Enumerate',
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
	 * Set a power of the lights
	 * @param int $address Network device address
	 * @param StandardLight[] $lights Lights
	 * @return mixed[] DPA request and response
	 * @throws DpaErrorException
	 * @throws EmptyResponseException
	 * @throws JsonException
	 * @throws UserErrorException
	 */
	public function setPower(int $address, array $lights): array {
		$array = [
			'mType' => 'iqrfLight_SetPower',
			'data' => [
				'req' => [
					'nAdr' => $address,
					'param' => [
						'lights' => [],
					],
					'returnVerbose' => true,
				],
			],
		];
		/**
		 * @var StandardLight $light Standard light
		 */
		foreach ($lights as $light) {
			$array['data']['req']['param']['lights'][] = $light->toArray();
		}
		$this->request->setRequest($array);
		return $this->wsClient->sendSync($this->request);
	}

	/**
	 * Get power of the lights
	 * @param int $address Network device address
	 * @param StandardLight[] $lights Indexes of lights
	 * @return mixed[] DPA request and response
	 * @throws DpaErrorException
	 * @throws EmptyResponseException
	 * @throws JsonException
	 * @throws UserErrorException
	 */
	public function getPower(int $address, array $lights): array {
		/**
		 * @var StandardLight $light Light
		 */
		foreach ($lights as $light) {
			$light->setPower(127);
		}
		return $this->setPower($address, $lights);
	}

	/**
	 * Increment power of the lights
	 * @param int $address Network device address
	 * @param StandardLight[] $lights Incremented power of lights
	 * @return mixed[] DPA request and response
	 * @throws DpaErrorException
	 * @throws EmptyResponseException
	 * @throws JsonException
	 * @throws UserErrorException
	 */
	public function incrementPower(int $address, array $lights): array {
		$array = [
			'mType' => 'iqrfLight_IncrementPower',
			'data' => [
				'req' => [
					'nAdr' => $address,
					'param' => [
						'lights' => [],
					],
				],
				'returnVerbose' => true,
			],
		];
		/**
		 * @var StandardLight $light Standard light
		 */
		foreach ($lights as $light) {
			$array['data']['req']['param']['lights'][] = $light->toArray();
		}
		$this->request->setRequest($array);
		return $this->wsClient->sendSync($this->request);
	}

	/**
	 * Decrement power of the lights
	 * @param int $address Network device address
	 * @param StandardLight[] $lights Decremented power of lights
	 * @return mixed[] DPA request and response
	 * @throws DpaErrorException
	 * @throws EmptyResponseException
	 * @throws JsonException
	 * @throws UserErrorException
	 */
	public function decrementPower(int $address, array $lights): array {
		$array = [
			'mType' => 'iqrfLight_DecrementPower',
			'data' => [
				'req' => [
					'nAdr' => $address,
					'param' => [
						'lights' => [],
					],
				],
				'returnVerbose' => true,
			],
		];
		/**
		 * @var StandardLight $light Standard light
		 */
		foreach ($lights as $light) {
			$array['data']['req']['param']['lights'][] = $light->toArray();
		}
		$this->request->setRequest($array);
		return $this->wsClient->sendSync($this->request);
	}

}
