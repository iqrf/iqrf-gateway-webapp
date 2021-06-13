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
use App\NetworkModule\Enums\WifiSecurityType;
use App\NetworkModule\Utils\NmCliConnection;
use stdClass;
use Throwable;

/**
 * WiFi connection entity
 */
final class WifiConnection implements INetworkManagerEntity {

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
	 * @var array<int, string> Seen BSSIDs
	 */
	private $bssids;

	/**
	 * @var WifiConnectionSecurity|null Wifi connection security entity
	 */
	private $security;

	/**
	 * @var int Channel
	 */
	private $channel;

	/**
	 * @var int Rate
	 */
	private $rate;

	/**
	 * Constructor
	 * @param string $ssid SSID
	 * @param WifiMode $mode WiFi network mode
	 * @param array<int, string> $bssids Seen BSSIDs
	 * @param WifiConnectionSecurity|null $security WiFi connection security entity
	 */
	public function __construct(string $ssid, WifiMode $mode, array $bssids, ?WifiConnectionSecurity $security) {
		$this->ssid = $ssid;
		$this->mode = $mode;
		$this->bssids = $bssids;
		$this->security = $security;
		$this->channel = 0;
		$this->rate = 0;
	}

	/**
	 * Deserializes WiFi connection entity from JSON
	 * @param stdClass $json JSON serialized entity
	 * @return WifiConnection WiFi connection entity
	 */
	public static function jsonDeserialize(stdClass $json): INetworkManagerEntity {
		$mode = WifiMode::fromScalar($json->mode);
		$security = ($json->security === null) ? null : WifiConnectionSecurity::jsonDeserialize($json->security);
		return new self($json->ssid, $mode, [], $security);
	}

	/**
	 * Serializes WiFi connection entity into JSON
	 * @return array<string, string|array> JSON serialized entity
	 */
	public function jsonSerialize(): array {
		return [
			'ssid' => $this->ssid,
			'mode' => $this->mode->toScalar(),
			'bssids' => $this->bssids,
			'security' => $this->security instanceof WifiConnectionSecurity ? $this->security->jsonSerialize() : null,
		];
	}

	/**
	 * Deserializes WiFI connection entity from nmcli connection configuration
	 * @param string $nmCli nmcli connection configuration
	 * @return WifiConnection WiFi connection entity
	 */
	public static function nmCliDeserialize(string $nmCli): INetworkManagerEntity {
		$array = NmCliConnection::decode($nmCli, self::NMCLI_PREFIX);
		$mode = WifiMode::fromScalar($array['mode']);
		$bssids = explode(',', $array['seen-bssids']);
		try {
			$security = WifiConnectionSecurity::nmCliDeserialize($nmCli);
		} catch (Throwable $e) {
			$security = null;
		}
		$connection = new self($array['ssid'], $mode, $bssids, $security);
		if (array_key_exists('channel', $array)) {
			$connection->setChannel(intval($array['channel']));
		}
		if (array_key_exists('rate', $array)) {
			$connection->setRate(intval($array['rate']));
		}
		return $connection;
	}

	/**
	 * Converts Wifi connection to Wifi network entity
	 * @return WifiNetwork Wifi network entity
	 */
	public function toWifiNetwork(): WifiNetwork {
		$channel = $this->channel ?? 0;
		$rate = sprintf('%d Mbit/s', $this->rate ?? 0);
		$security = $this->security->jsonSerialize()['type'];
		if ($security === WifiSecurityType::OPEN()->toScalar()) {
			$security = WifiSecurity::OPEN();
		} elseif ($security === WifiSecurityType::WEP()->toScalar() ||
			$security === WifiSecurityType::LEAP()->toScalar()) {
			$security = WifiSecurity::WEP();
		} elseif ($security === WifiSecurityType::WPA_EAP()->toScalar()) {
			$security = WifiSecurity::WPA2_ENTERPRISE();
		} else {
			$security = WifiSecurity::WPA2_PERSONAL();
		}
		return new WifiNetwork(false, $this->bssids[0], $this->ssid, $this->mode, $channel, $rate, 100, $security);
	}

	/**
	 * Serializes WiFi connection entity into nmcli configuration string
	 * @return string nmcli configuration
	 */
	public function nmCliSerialize(): string {
		$array = [
			'ssid' => $this->ssid,
			'mode' => $this->mode->toScalar(),
		];
		$string = NmCliConnection::encode($array, self::NMCLI_PREFIX);
		if ($this->security !== null) {
			$string .= $this->security->nmCliSerialize();
		}
		return $string;
	}

	/**
	 * Returns wifi connection mode
	 * @return WifiMode Connection mode
	 */
	public function getMode(): WifiMode {
		return $this->mode;
	}

	/**
	 * Sets connection mode
	 * @param WifiMode $mode Connection mode
	 */
	public function setMode(WifiMode $mode): void {
		$this->mode = $mode;
	}

	/**
	 * Returns channel
	 * @return int|null Channel
	 */
	public function getChannel(): ?int {
		return $this->rate;
	}

	/**
	 * Sets channel
	 * @param int $channel Channel
	 */
	public function setChannel(int $channel): void {
		$this->channel = $channel;
	}

	/**
	 * Returns rate
	 * @return int|null rate
	 */
	public function getRate(): ?int {
		return $this->rate;
	}

	/**
	 * Sets rate
	 * @param int $rate Rate
	 */
	public function setRate(int $rate): void {
		$this->rate = $rate;
	}

}
