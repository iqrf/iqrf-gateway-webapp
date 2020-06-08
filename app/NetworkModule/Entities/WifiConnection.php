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
use JsonSerializable;
use Nette\Utils\Strings;

/**
 * WiFi connection entity
 */
final class WifiConnection implements JsonSerializable {

	/**
	 * @var string SSID
	 */
	private $ssid;

	/**
	 * @var WifiMode WiFI network mode
	 */
	private $mode;

	public function __construct(string $ssid, WifiMode $mode) {
		$this->ssid = $ssid;
		$this->mode = $mode;
	}

	/**
	 * Creates a new WiFI connection entity from nmcli connection configuration
	 * @param string $nmCli nmcli connection configuration
	 * @return WifiConnection IPv6 connection entity
	 */
	public static function fromNmCli(string $nmCli): self {
		$array = explode(PHP_EOL, Strings::trim($nmCli));
		foreach ($array as $i => $row) {
			$temp = explode(':', $row, 2);
			if (Strings::startsWith($temp[0], '802-11-wireless.')) {
				$key = Strings::replace($temp[0], '~802-11-wireless\.~', '');
				$array[$key] = $temp[1];
			}
			unset($array[$i]);
		}
		$mode = WifiMode::INFRA();
		return new static($array['ssid'], $mode);
	}

	/**
	 * Returns WiFi netwotk mode
	 * @return WifiMode WiFi network mode
	 */
	public function getMode(): WifiMode {
		return $this->mode;
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
		];
	}

}
