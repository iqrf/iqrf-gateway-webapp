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
	private string $apn;

	/**
	 * @var string|null Username
	 */
	private ?string $username;

	/**
	 * @var string|null Password
	 */
	private ?string $password;

	/**
	 * @var string|null SIM PIN
	 */
	private ?string $pin;

	/**
	 * Constructor
	 * @param string $apn Access point name
	 * @param string|null $username Username
	 * @param string|null $password Password
	 * @param string|null $pin SIM PIN
	 */
	public function __construct(string $apn, ?string $username = null, ?string $password = null, ?string $pin = null) {
		$this->apn = $apn;
		$this->username = $username;
		$this->password = $password;
		$this->pin = $pin;
	}

	/**
	 * Deserializes GSM connection entity from JSON
	 * @param stdClass $json JSON serialized entity
	 * @return GSMConnection GSM connection entity
	 */
	public static function jsonDeserialize(stdClass $json): INetworkManagerEntity {
		return new self($json->apn, $json->username, $json->password, $json->pin);
	}

	/**
	 * Serializes GSM connection entity into JSON
	 * @return array<string, string|null> JSON serialized entity
	 */
	public function jsonSerialize(): array {
		return [
			'apn' => $this->apn,
			'username' => $this->username,
			'password' => $this->password,
			'pin' => $this->pin,
		];
	}

	/**
	 * Deserializes GSM connection from nmcli connection string
	 * @param string $nmCli nmcli connection configuration
	 * @return GSMConnection GSM connection entity
	 */
	public static function nmCliDeserialize(string $nmCli): INetworkManagerEntity {
		$array = NmCliConnection::decode($nmCli, self::NMCLI_PREFIX);
		$username = $array['username'] ?? null;
		$password = $array['password'] ?? null;
		$pin = $array['pin'] ?? null;
		return new self($array['apn'], $username, $password, $pin);
	}

	/**
	 * Serializes GSM connection entity into nmcli configuration string
	 * @return string nmcli configuration
	 */
	public function nmCliSerialize(): string {
		$array = $this->jsonSerialize();
		$array['username'] ??= '';
		$array['password'] ??= '';
		$array['pin'] ??= '';
		return NmCliConnection::encode($array, self::NMCLI_PREFIX);
	}

}
