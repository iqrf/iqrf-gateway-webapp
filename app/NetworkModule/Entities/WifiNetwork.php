<?php

/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
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
use App\NetworkModule\Enums\WifiSecurity;
use JsonSerializable;
use Nette\Utils\Strings;

/**
 * WiFi network entity
 */
final readonly class WifiNetwork implements JsonSerializable {

	/**
	 * Constructor
	 * @param bool $inUse Is in use?
	 * @param string $bssid BSSID
	 * @param string $ssid SSID
	 * @param WifiMode $mode Mode
	 * @param int $channel Channel
	 * @param string $rate Speed rate
	 * @param int $signal Signal strength
	 * @param WifiSecurity $security Security
	 */
	public function __construct(
		private bool $inUse,
		private string $bssid,
		private string $ssid,
		private WifiMode $mode,
		private int $channel,
		private string $rate,
		private int $signal,
		private WifiSecurity $security,
	) {
	}

	/**
	 * Deserializes WiFi network entity from nmcli row
	 * @param string $nmCli nmcli row
	 * @return WifiNetwork WiFi network
	 */
	public static function nmCliDeserialize(string $nmCli): self {
		$pattern = '#^(?\'inUse\'[^:]*):(?\'bssid\'([A-Fa-f\d]{2}\\\\:){5}[A-Fa-f\d]{2}):(?\'ssid\'[^:]*):(?\'mode\'[^:]*):(?\'channel\'[^:]*):(?\'rate\'[^:]*):(?\'signal\'[^:]*):(?\'security\'[^:]*)$#';
		$matches = Strings::match($nmCli, $pattern);
		$inUse = $matches['inUse'] === '*';
		$bssid = Strings::replace($matches['bssid'], '#\\\\:#', ':');
		$ssid = Strings::replace($matches['ssid'], '#\\\\:#', ':');
		$mode = WifiMode::fromNetworkList($matches['mode']);
		$channel = (int) $matches['channel'];
		$signal = (int) $matches['signal'];
		$security = WifiSecurity::fromNmCli($matches['security']);
		return new self($inUse, $bssid, $ssid, $mode, $channel, $matches['rate'], $signal, $security);
	}

	/**
	 * Returns the network's security
	 * @return WifiSecurity Network security
	 */
	public function getSecurity(): WifiSecurity {
		return $this->security;
	}

	/**
	 * Serializes WiFi network entity into JSON
	 * @return array{
	 *     inUse: bool,
	 *     bssid: string,
	 *     ssid: string,
	 *     mode: string,
	 *     channel: int,
	 *     rate: string,
	 *     signal: int,
	 *     security: string,
	 * } JSON serialized entity
	 */
	public function jsonSerialize(): array {
		return [
			'inUse' => $this->inUse,
			'bssid' => $this->bssid,
			'ssid' => $this->ssid,
			'mode' => $this->mode->value,
			'channel' => $this->channel,
			'rate' => $this->rate,
			'signal' => $this->signal,
			'security' => $this->security->value,
		];
	}

}
