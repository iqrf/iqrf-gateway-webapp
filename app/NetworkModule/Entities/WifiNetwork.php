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
use App\NetworkModule\Enums\WifiSecurity;
use JsonSerializable;

/**
 * WiFi network entity
 */
final class WifiNetwork implements JsonSerializable {

	/**
	 * @var bool Is in use?
	 */
	private $inUse;

	/**
	 * @var string BSSID (MAC address)
	 */
	private $bssid;

	/**
	 * @var string SSID
	 */
	private $ssid;

	/**
	 * @var WifiMode Mode
	 */
	private $mode;

	/**
	 * @var int Channel
	 */
	private $channel;

	/**
	 * @var string Speed rate
	 */
	private $rate;

	/**
	 * @var int Signal strength
	 */
	private $signal;

	/**
	 * @var WifiSecurity Security
	 */
	private $security;

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
	public function __construct(bool $inUse, string $bssid, string $ssid, WifiMode $mode, int $channel, string $rate, int $signal, WifiSecurity $security) {
		$this->inUse = $inUse;
		$this->bssid = $bssid;
		$this->ssid = $ssid;
		$this->mode = $mode;
		$this->channel = $channel;
		$this->rate = $rate;
		$this->signal = $signal;
		$this->security = $security;
	}

	/**
	 * Creates a new WiFi network entity from nmcli
	 * @param string $nmCli nmcli
	 * @return WifiNetwork WiFi network
	 */
	public static function fromNmCli(string $nmCli): self {
		$pattern = '/^(?\'inUse\'[^:]*):(?\'bssid\'([A-Fa-f\d]{2}\\\\:){5}[A-Fa-f\d]{2}):(?\'ssid\'[^:]*):(?\'mode\'[^:]*):(?\'channel\'[^:]*):(?\'rate\'[^:]*):(?\'signal\'[^:]*):(?\'bars\'[^:]*):(?\'security\'[^:]*)$/';
		preg_match($pattern, $nmCli, $matches);
		$inUse = $matches['inUse'] === '*';
		$bssid = preg_replace('/\\\\:/', ':', $matches['bssid']);
		$ssid = preg_replace('/\\\\:/', ':', $matches['ssid']);
		$mode = WifiMode::fromScalar($matches['mode']);
		$channel = (int) $matches['channel'];
		$signal = (int) $matches['signal'];
		$security = WifiSecurity::fromNmCli($matches['security']);
		return new static($inUse, $bssid, $ssid, $mode, $channel, $matches['rate'], $signal, $security);
	}

	/**
	 * Returns JSON serialized data
	 * @return array<string,mixed> JSON serialized data
	 */
	public function jsonSerialize(): array {
		return [
			'inUse' => $this->inUse,
			'bssid' => $this->bssid,
			'ssid' => $this->ssid,
			'mode' => $this->mode->toScalar(),
			'channel' => $this->channel,
			'rate' => $this->rate,
			'signal' => $this->signal,
			'security' => $this->security->toScalar(),
		];
	}

}
