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
use Nette\Utils\Strings;

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
	 * Checks if the WiFI network is used
	 * @return bool Is used?
	 */
	public function isUsed(): bool {
		return $this->inUse;
	}

	/**
	 * Returns the network's BSSID (Basic Services Set Identifier)
	 * @return string BSSID
	 */
	public function getBssid(): string {
		return $this->bssid;
	}

	/**
	 * Returns the network's SSID (Service Set Identifier)
	 * @return string SSID
	 */
	public function getSsid(): string {
		return $this->ssid;
	}

	/**
	 * Returns the network's mode
	 * @return WifiMode Network's mode
	 */
	public function getMode(): WifiMode {
		return $this->mode;
	}

	/**
	 * Returns the network's channel
	 * @return int Network's channel
	 */
	public function getChannel(): int {
		return $this->channel;
	}

	/**
	 * Returns the network's speed rate
	 * @return string Speed rate
	 */
	public function getRate(): string {
		return $this->rate;
	}

	/**
	 * Returns the network signal strength
	 * @return int Signal strength
	 */
	public function getSignal(): int {
		return $this->signal;
	}

	/**
	 * Returns the network's security
	 * @return WifiSecurity Network security
	 */
	public function getSecurity(): WifiSecurity {
		return $this->security;
	}

	/**
	 * Deserializes WiFi network entity from nmcli row
	 * @param string $nmCli nmcli row
	 * @return WifiNetwork WiFi network
	 */
	public static function nmCliDeserialize(string $nmCli): self {
		$pattern = '/^(?\'inUse\'[^:]*):(?\'bssid\'([A-Fa-f\d]{2}\\\\:){5}[A-Fa-f\d]{2}):(?\'ssid\'[^:]*):(?\'mode\'[^:]*):(?\'channel\'[^:]*):(?\'rate\'[^:]*):(?\'signal\'[^:]*):(?\'bars\'[^:]*):(?\'security\'[^:]*)$/';
		$matches = Strings::match($nmCli, $pattern);
		$inUse = $matches['inUse'] === '*';
		$bssid = Strings::replace($matches['bssid'], '/\\\\:/', ':');
		$ssid = Strings::replace($matches['ssid'], '/\\\\:/', ':');
		$mode = WifiMode::fromNetworkList($matches['mode']);
		$channel = (int) $matches['channel'];
		$signal = (int) $matches['signal'];
		$security = WifiSecurity::fromNmCli($matches['security']);
		return new self($inUse, $bssid, $ssid, $mode, $channel, $matches['rate'], $signal, $security);
	}

	/**
	 * Serializes WiFi network entity into JSON
	 * @return array<string, bool|int|string> JSON serialized entity
	 */
	public function jsonSerialize(): array {
		return [
			'inUse' => $this->inUse,
			'bssid' => $this->bssid,
			'ssid' => $this->ssid,
			'mode' => (string) $this->mode->toScalar(),
			'channel' => $this->channel,
			'rate' => $this->rate,
			'signal' => $this->signal,
			'security' => (string) $this->security->toScalar(),
		];
	}

}
