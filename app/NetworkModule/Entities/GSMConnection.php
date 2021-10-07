<?php

/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
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

namespace App\NetworkModule\Entities;

use App\NetworkModule\Utils\NmCliConnection;
use stdClass;

/**
 * GSM connection entity
 */
final class GSMConnection implements INetworkManagerEntity {

	/**
	 * nmcli configuration prefix
	 */
	private const NMCLI_PREFIX = 'gsm';

	/**
	 * @var string GSM APN
	 */
	private $apn;

	/**
	 * @var string GSM number
	 */
	private $number;

	/**
	 * @var string|null Username
	 */
	private $username;

	/**
	 * @var string|null Password
	 */
	private $password;

	/**
	 * Constructor
	 * @param string $apn Access point name
	 * @param string $number Number
	 * @param string|null $username Username
	 * @param string|null $password Password
	 */
	public function __construct(string $apn, string $number, ?string $username = null, ?string $password = null) {
		$this->apn = $apn;
		$this->number = $number;
		$this->username = $username;
		$this->password = $password;
	}

	/**
	 * Deserializes GSM connection entity from JSON
	 * @param stdClass $json JSON serialized entity
	 * @return GSMConnection GSM connection entity
	 */
	public static function jsonDeserialize(stdClass $json): INetworkManagerEntity {
		return new self($json->apn, $json->number, $json->username, $json->password);
	}

	/**
	 * Serializes GSM connection entity into JSON
	 * @return array<string, string> JSON serialized entity
	 */
	public function jsonSerialize(): array {
		$array = [
			'apn' => $this->apn,
			'number' => $this->number,
		];
		if ($this->username !== null) {
			$array['username'] = $this->username;
		}
		if ($this->password !== null) {
			$array['password'] = $this->password;
		}
		return $array;
	}

	/**
	 * Deserializes GSM connection from nmcli connection string
	 * @param string $nmCli nmcli connection configuration
	 * @return GSMConnection GSM connection entity
	 */
	public static function nmCliDeserialize(string $nmCli): INetworkManagerEntity {
		$array = NmCliConnection::decode($nmCli, self::NMCLI_PREFIX);
		$username = array_key_exists('username', $array) ? $array['username'] : null;
		$password = array_key_exists('password', $array) ? $array['password'] : null;
		return new self($array['apn'], $array['number'], $username, $password);
	}

	/**
	 * Serializes GSM connection entity into nmcli configuration string
	 * @return string nmcli configuration
	 */
	public function nmCliSerialize(): string {
		$array = $this->jsonSerialize();
		return NmCliConnection::encode($array, self::NMCLI_PREFIX);
	}

}
