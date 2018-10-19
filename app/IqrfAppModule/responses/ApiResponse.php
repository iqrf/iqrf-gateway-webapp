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

namespace App\IqrfAppModule\Responses;

use App\IqrfAppModule\Exception as IqrfException;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Ratchet\RFC6455\Messaging\MessageInterface;

/**
 * JSON API request
 */
class ApiResponse {

	/**
	 * @var array DPA exceptions
	 */
	private $exceptions = [
		-8 => IqrfException\ExclusiveAccessException::class,
		-7 => IqrfException\BadResponseException::class,
		-6 => IqrfException\BadRequestException::class,
		-5 => IqrfException\InterfaceBusyException::class,
		-4 => IqrfException\InterfaceErrorException::class,
		-3 => IqrfException\AbortedException::class,
		-2 => IqrfException\InterfaceQueueFullException::class,
		-1 => IqrfException\TimeoutException::class,
		1 => IqrfException\GeneralFailureException::class,
		2 => IqrfException\IncorrectPcmdException::class,
		3 => IqrfException\IncorrectPnumException::class,
		4 => IqrfException\IncorrectAddressException::class,
		5 => IqrfException\IncorrectDataLengthException::class,
		6 => IqrfException\IncorrectDataException::class,
		7 => IqrfException\IncorrectHwpidUsedException::class,
		8 => IqrfException\IncorrectNadrException::class,
		9 => IqrfException\CustomHandlerConsumedInterfaceDataException::class,
		10 => IqrfException\MissingCustomDpaHandlerException::class,
	];

	/**
	 * @var array JSON API response
	 */
	protected $response;

	/**
	 * Check status from JSON API response
	 * @throws IqrfException\DpaErrorException
	 * @throws IqrfException\UserErrorException
	 */
	public function checkStatus(): void {
		$status = $this->response['data']['status'];
		if ($status === 0) {
			return;
		}
		if (array_key_exists($status, $this->exceptions)) {
			throw new $this->exceptions[$status];
		} else {
			throw new IqrfException\UserErrorException();
		}
	}

	/**
	 * Set JSON API response
	 * @param string $response JSON API response
	 * @throws IqrfException\DpaErrorException
	 * @throws IqrfException\UserErrorException
	 * @throws JsonException
	 */
	public function setResponse(string $response): void {
		$this->response = Json::decode($response, Json::FORCE_ARRAY);
		$this->checkStatus();
	}

	/**
	 * Convert JSON API request to a array
	 * @return array JSON API request
	 */
	public function toArray(): array {
		return $this->response;
	}

	/**
	 * Convert JSON DPA request to JSON string
	 * @param bool $pretty Pretty formatted JSON
	 * @return string JSON string
	 * @throws JsonException
	 */
	public function toJson(bool $pretty = false): string {
		$options = $pretty ? Json::PRETTY : 0;
		return Json::encode($this->response, $options);
	}

}
