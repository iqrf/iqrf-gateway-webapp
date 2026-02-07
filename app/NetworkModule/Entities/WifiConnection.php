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

namespace App\NetworkModule\Entities;

use App\NetworkModule\Enums\WifiMode;
use App\NetworkModule\Utils\NmCliConnection;
use stdClass;
use Throwable;

/**
 * WiFi connection entity
 */
final readonly class WifiConnection implements INetworkManagerEntity {

	/**
	 * nmcli configuration prefix
	 */
	private const NMCLI_PREFIX = '802-11-wireless';

	/**
	 * Constructor
	 * @param string $ssid SSID
	 * @param WifiMode $mode WiFi network mode
	 * @param array<int, string> $bssids Seen BSSIDs
	 * @param WifiConnectionSecurity|null $security WiFi connection security entity
	 */
	public function __construct(
		private string $ssid,
		private WifiMode $mode,
		private array $bssids,
		private ?WifiConnectionSecurity $security,
	) {
	}

	/**
	 * Deserializes WiFi connection entity from JSON
	 * @param stdClass $json JSON serialized entity
	 * @return WifiConnection WiFi connection entity
	 */
	public static function jsonDeserialize(stdClass $json): INetworkManagerEntity {
		$mode = WifiMode::from($json->mode);
		$security = ($json->security === null) ? null : WifiConnectionSecurity::jsonDeserialize($json->security);
		return new self($json->ssid, $mode, [], $security);
	}

	/**
	 * Deserializes WiFI connection entity from nmcli connection configuration
	 * @param array<string, array<string, array<string>|string>> $nmCli nmcli connection configuration
	 * @return WifiConnection WiFi connection entity
	 */
	public static function nmCliDeserialize(array $nmCli): INetworkManagerEntity {
		/**
		 * @var array{
		 *     ssid: string,
		 *     mode: string,
		 *     'seen-bssids': string,
		 * } $array Parsed nmcli configuration array
		 */
		$array = $nmCli[self::NMCLI_PREFIX];
		$mode = WifiMode::from($array['mode']);
		$bssids = explode(',', $array['seen-bssids']);
		try {
			$security = WifiConnectionSecurity::nmCliDeserialize($nmCli);
		} catch (Throwable) {
			$security = null;
		}
		return new self($array['ssid'], $mode, $bssids, $security);
	}

	/**
	 * Serializes WiFi connection entity into JSON
	 * @return array{
	 *     ssid: string,
	 *     mode: string,
	 *     bssids: array<int, string>,
	 *     security: array{
	 *         type: string,
	 *         psk: string|null,
	 *         leap?: array{
	 *             username: string,
	 *             password: string,
	 *         },
	 *         wep?: array{
	 *             type: string,
	 *             index: int,
	 *             keys: array<string>,
	 *         },
	 *         eap?: array{
	 *             phaseOneMethod: string|null,
	 *             phaseTwoMethod: string|null,
	 *             anonymousIdentity: string,
	 *             cert: string,
	 *             identity: string,
	 *             password: string,
	 *         },
	 *     }|null,
	 * } JSON serialized entity
	 */
	public function jsonSerialize(): array {
		return [
			'ssid' => $this->ssid,
			'mode' => $this->mode->value,
			'bssids' => $this->bssids,
			'security' => $this->security instanceof WifiConnectionSecurity ? $this->security->jsonSerialize() : null,
		];
	}

	/**
	 * Serializes WiFi connection entity into nmcli configuration string
	 * @return string nmcli configuration
	 */
	public function nmCliSerialize(): string {
		$array = [
			'ssid' => $this->ssid,
			'mode' => $this->mode->value,
		];
		$string = NmCliConnection::encode($array, self::NMCLI_PREFIX);
		if ($this->security instanceof WifiConnectionSecurity) {
			$string .= $this->security->nmCliSerialize();
		}
		return $string;
	}

}
