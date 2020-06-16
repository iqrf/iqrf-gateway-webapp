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

namespace App\NetworkModule\Entities;

use App\NetworkModule\Enums\WifiMode;
use App\NetworkModule\Utils\NmCliConnection;
use JsonSerializable;
use stdClass;

/**
 * WiFi connection entity
 */
final class WifiConnection implements JsonSerializable {

	/**
	 * nmcli configuration prefix
	 */
	private const NMCLI_PREFIX = '802-11-wireless';

	/**
	 * @var string SSID
	 */
	private $ssid;

	/**
	 * @var WifiMode WiFi network mode
	 */
	private $mode;

	/**
	 * @var WifiConnectionSecurity Wifi connection security entity
	 */
	private $security;

	/**
	 * Constructor
	 * @param string $ssid SSID
	 * @param WifiMode $mode WiFi network mode
	 * @param WifiConnectionSecurity $security WiFi connection security entity
	 */
	public function __construct(string $ssid, WifiMode $mode, WifiConnectionSecurity $security) {
		$this->ssid = $ssid;
		$this->mode = $mode;
		$this->security = $security;
	}

	/**
	 * Sets the values from the network connection configuration JSON
	 * @param stdClass $json Values from the network connection configuration JSON
	 */
	public function fromJson(stdClass $json): void {
		$this->ssid = $json->ssid;
		$this->mode = WifiMode::fromScalar($json->mode);
		$this->security->fromJson($json->security);
	}

	/**
	 * Creates a new WiFI connection entity from nmcli connection configuration
	 * @param string $nmCli nmcli connection configuration
	 * @return WifiConnection WiFi connection entity
	 */
	public static function fromNmCli(string $nmCli): self {
		$array = NmCliConnection::decode($nmCli, self::NMCLI_PREFIX);
		$mode = WifiMode::fromScalar($array['mode']);
		$security = WifiConnectionSecurity::fromNmCli($nmCli);
		return new static($array['ssid'], $mode, $security);
	}

	/**
	 * Returns WiFi network mode
	 * @return WifiMode WiFi network mode
	 */
	public function getMode(): WifiMode {
		return $this->mode;
	}

	/**
	 * Returns WiFi connection security entity
	 * @return WifiConnectionSecurity WiFi connection security entity
	 */
	public function getSecurity(): WifiConnectionSecurity {
		return $this->security;
	}

	/**
	 * Returns SSID
	 * @return string SSID
	 */
	public function getSsid(): string {
		return $this->ssid;
	}

	/**
	 * Returns JSON serialized data
	 * @return array<string, string|array> JSON serialized data
	 */
	public function jsonSerialize(): array {
		return [
			'ssid' => $this->ssid,
			'mode' => $this->mode->toScalar(),
			'security' => $this->security->jsonSerialize(),
		];
	}

	/**
	 * Converts WiFi connection entity to nmcli configuration string
	 * @return string nmcli configuration
	 */
	public function toNmCli(): string {
		$array = [
			'ssid' => $this->ssid,
			'mode' => $this->mode->toScalar(),
		];
		$string = NmCliConnection::encode($array, self::NMCLI_PREFIX);
		$string .= $this->security->toNmCli();
		return $string;
	}

}
