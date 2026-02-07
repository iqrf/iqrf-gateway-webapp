<?php

/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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

namespace App\NetworkModule\Entities\WifiSecurity;

use App\NetworkModule\Entities\INetworkManagerEntity;
use App\NetworkModule\Entities\WifiConnectionSecurity;
use App\NetworkModule\Utils\NmCliConnection;
use stdClass;

/**
 * Cisco LEAP (Lightweight Extensible Authentication Protocol) entity
 */
class Leap implements INetworkManagerEntity {

	/**
	 * Constructor
	 * @param string $username LEAP username
	 * @param string $password LEAP password
	 */
	public function __construct(
		private readonly string $username,
		private readonly string $password,
	) {
	}

	/**
	 * Deserializes Cisco LEAP entity from JSON
	 * @param stdClass $json JSON serialized data
	 * @return INetworkManagerEntity WEP entity
	 */
	public static function jsonDeserialize(stdClass $json): INetworkManagerEntity {
		return new self($json->username, $json->password);
	}

	/**
	 * Deserializes Cisco LEAP entity from nmcli configuration
	 * @param array<string, array<string, array<string>|string>> $nmCli nmcli configuration
	 * @return INetworkManagerEntity WEP entity
	 */
	public static function nmCliDeserialize(array $nmCli): INetworkManagerEntity {
		/**
		 * @var array{
		 *     'leap-username': string,
		 *     'leap-password': string,
		 * } $array Parsed nmcli configuration array
		 */
		$array = $nmCli[WifiConnectionSecurity::NMCLI_PREFIX];
		return new self($array['leap-username'], $array['leap-password']);
	}

	/**
	 * Serializes Cisco LEAP entity into JSON
	 * @return array{username: string, password: string} JSON serialized data
	 */
	public function jsonSerialize(): array {
		return [
			'username' => $this->username,
			'password' => $this->password,
		];
	}

	/**
	 * Serializes Cisco LEAP entity into nmcli configuration string
	 * @return string nmcli configuration
	 */
	public function nmCliSerialize(): string {
		$array = [
			'leap-password' => $this->password,
			'leap-username' => $this->username,
		];
		return NmCliConnection::encode($array, WifiConnectionSecurity::NMCLI_PREFIX);
	}

}
