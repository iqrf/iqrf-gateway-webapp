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

namespace App\IqrfNetModule\Responses;

use App\IqrfNetModule\Exceptions\AbortedException;
use App\IqrfNetModule\Exceptions\BadRequestException;
use App\IqrfNetModule\Exceptions\BadResponseException;
use App\IqrfNetModule\Exceptions\CustomHandlerConsumedInterfaceDataException;
use App\IqrfNetModule\Exceptions\DpaErrorException;
use App\IqrfNetModule\Exceptions\ExclusiveAccessException;
use App\IqrfNetModule\Exceptions\GeneralFailureException;
use App\IqrfNetModule\Exceptions\IncorrectAddressException;
use App\IqrfNetModule\Exceptions\IncorrectDataException;
use App\IqrfNetModule\Exceptions\IncorrectDataLengthException;
use App\IqrfNetModule\Exceptions\IncorrectHwpidUsedException;
use App\IqrfNetModule\Exceptions\IncorrectNadrException;
use App\IqrfNetModule\Exceptions\IncorrectPcmdException;
use App\IqrfNetModule\Exceptions\IncorrectPnumException;
use App\IqrfNetModule\Exceptions\InterfaceBusyException;
use App\IqrfNetModule\Exceptions\InterfaceErrorException;
use App\IqrfNetModule\Exceptions\InterfaceQueueFullException;
use App\IqrfNetModule\Exceptions\MissingCustomDpaHandlerException;
use App\IqrfNetModule\Exceptions\TimeoutException;
use App\IqrfNetModule\Exceptions\UserErrorException;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

/**
 * IQRF JSON API request
 */
class ApiResponse {

	/**
	 * @var string[] IQRF JSON API exceptions
	 */
	private $exceptions = [
		-8 => ExclusiveAccessException::class,
		-7 => BadResponseException::class,
		-6 => BadRequestException::class,
		-5 => InterfaceBusyException::class,
		-4 => InterfaceErrorException::class,
		-3 => AbortedException::class,
		-2 => InterfaceQueueFullException::class,
		-1 => TimeoutException::class,
		1 => GeneralFailureException::class,
		2 => IncorrectPcmdException::class,
		3 => IncorrectPnumException::class,
		4 => IncorrectAddressException::class,
		5 => IncorrectDataLengthException::class,
		6 => IncorrectDataException::class,
		7 => IncorrectHwpidUsedException::class,
		8 => IncorrectNadrException::class,
		9 => CustomHandlerConsumedInterfaceDataException::class,
		10 => MissingCustomDpaHandlerException::class,
	];

	/**
	 * @var mixed[] JSON API response
	 */
	protected $response;

	/**
	 * Checks a status from the IQRF JSON API response
	 * @throws DpaErrorException
	 * @throws UserErrorException
	 */
	public function checkStatus(): void {
		$status = $this->response['data']['status'];
		if ($status === 0) {
			return;
		}
		if (array_key_exists($status, $this->exceptions)) {
			throw new $this->exceptions[$status]();
		}
		throw new UserErrorException();
	}

	/**
	 * Sets the IQRF JSON API response
	 * @param string $response IQRF JSON API response
	 */
	public function setResponse(string $response): void {
		try {
			$this->response = Json::decode($response, Json::FORCE_ARRAY);
		} catch (JsonException $e) {
			$this->response = [];
		}
	}

	/**
	 * Converts IQRF JSON API request to a array
	 * @return mixed[] JSON API request
	 */
	public function toArray(): array {
		return $this->response;
	}

	/**
	 * Converts IQRF JSON API request to a JSON string
	 * @param bool $pretty Pretty formatted JSON
	 * @return string JSON string
	 * @throws JsonException
	 */
	public function toJson(bool $pretty = false): string {
		$options = $pretty ? Json::PRETTY : 0;
		return Json::encode($this->response, $options);
	}

}
