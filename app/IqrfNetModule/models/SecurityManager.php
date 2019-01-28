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

use App\IqrfNetModule\Enums\DataFormat;
use App\IqrfNetModule\Exceptions as IqrfException;
use App\IqrfNetModule\Requests\ApiRequest;
use Nette\SmartObject;
use Nette\Utils\JsonException;
use Nette\Utils\Strings;

/**
 * Tool for setting an access password and an user key
 */
class SecurityManager {

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
	 * Set an access password
	 * @param string $password An access password
	 * @param string $inputFormat Input data format (ASCII or HEX)
	 * @return mixed[] DPA request and response
	 * @throws IqrfException\DpaErrorException
	 * @throws IqrfException\EmptyResponseException
	 * @throws IqrfException\UnsupportedInputFormatException
	 * @throws IqrfException\UserErrorException
	 * @throws JsonException
	 */
	public function setAccessPassword(string $password = '', string $inputFormat = DataFormat::ASCII): array {
		return $this->setSecurity($password, $inputFormat, 0);
	}

	/**
	 * Set an user key
	 * @param string $password An user key
	 * @param string $inputFormat Input data format (ASCII or HEX)
	 * @return mixed[] DPA request and response
	 * @throws IqrfException\DpaErrorException
	 * @throws IqrfException\EmptyResponseException
	 * @throws IqrfException\UnsupportedInputFormatException
	 * @throws IqrfException\UserErrorException
	 * @throws JsonException
	 */
	public function setUserKey(string $password = '', string $inputFormat = DataFormat::ASCII): array {
		return $this->setSecurity($password, $inputFormat, 1);
	}

	/**
	 * Set IQMESH security
	 * @param string $password An access password or an user key
	 * @param string $inputFormat Input data format (ASCII or HEX)
	 * @param int $type Security type (access password, user key)
	 * @return mixed[] DPA request and response
	 * @throws IqrfException\DpaErrorException
	 * @throws IqrfException\EmptyResponseException
	 * @throws IqrfException\UnsupportedInputFormatException
	 * @throws IqrfException\UserErrorException
	 * @throws JsonException
	 */
	private function setSecurity(string $password, string $inputFormat, int $type): array {
		$array = [
			'mType' => 'iqrfEmbedOs_SetSecurity',
			'data' => [
				'req' => [
					'nAdr' => 0,
					'param' => [
						'type' => $type,
						'data' => $this->convertToHex($password, $inputFormat),
					],
				],
				'returnVerbose' => true,
			],
		];
		$this->request->setRequest($array);
		return $this->wsClient->sendSync($this->request);
	}

	/**
	 * Convert an access password or an user key to HEX format
	 * @param string $password Access password or user key
	 * @param string $inputFormat Input data format (ASCII or HEX)
	 * @return mixed[] Converted an access password or an user key
	 * @throws IqrfException\UnsupportedInputFormatException
	 */
	private function convertToHex(string $password, string $inputFormat): array {
		if ($inputFormat === DataFormat::ASCII) {
			$data = implode(unpack('H*', $password));
		} elseif ($inputFormat === DataFormat::HEX) {
			$data = $password;
		} else {
			throw new IqrfException\UnsupportedInputFormatException();
		}
		$array = explode('.', Strings::trim(Strings::lower(chunk_split(Strings::padLeft($data, 32, '0'), 2, '.')), '.'));
		foreach ($array as &$chunk) {
			$chunk = hexdec($chunk);
		}
		return $array;
	}

}
