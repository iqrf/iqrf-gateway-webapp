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

use App\NetworkModule\Entities\WifiSecurity\Eap;
use App\NetworkModule\Entities\WifiSecurity\Leap;
use App\NetworkModule\Entities\WifiSecurity\Wep;
use App\NetworkModule\Enums\WifiSecurityType;
use App\NetworkModule\Utils\NmCliConnection;
use stdClass;

/**
 * WiFi connection security entity
 */
final readonly class WifiConnectionSecurity implements INetworkManagerEntity {

	/**
	 * nmcli 802-11-wireless security configuration prefix
	 */
	public const NMCLI_PREFIX = '802-11-wireless-security';

	/**
	 * Constructor
	 * @param WifiSecurityType $type WiFi security type
	 * @param string|null $psk Pre-shared key
	 * @param Leap|null $leap Cisco LEAP entity
	 * @param Wep|null $wep WEP entity
	 */
	public function __construct(
		private WifiSecurityType $type,
		private ?string $psk,
		private ?Leap $leap,
		private ?Wep $wep,
		private ?Eap $eap,
	) {
	}

	/**
	 * Deserializes WiFi connection security entity from JSON
	 * @param stdClass $json JSON serialized entity
	 * @return WifiConnectionSecurity WiFI connection security entity
	 */
	public static function jsonDeserialize(stdClass $json): INetworkManagerEntity {
		$type = WifiSecurityType::from($json->type);
		if (isset($json->leap)) {
			$leap = Leap::jsonDeserialize($json->leap);
			assert($leap instanceof Leap);
		}
		if (isset($json->wep)) {
			$wep = Wep::jsonDeserialize($json->wep);
			assert($wep instanceof Wep);
		}
		if ($type === WifiSecurityType::WPA_EAP) {
			$eap = Eap::jsonDeserialize($json->eap);
			assert($eap instanceof Eap);
		}
		return new self($type, $json->psk, $leap ?? null, $wep ?? null, $eap ?? null);
	}

	/**
	 * Deserializes WiFi connection security entity from nmcli connection configuration
	 * @param array<string, array<string, array<string>|string>> $nmCli nmcli connection configuration
	 * @return WifiConnectionSecurity WiFI connection security entity
	 */
	public static function nmCliDeserialize(array $nmCli): INetworkManagerEntity {
		$array = $nmCli[self::NMCLI_PREFIX] ?? [];
		$type = WifiSecurityType::nmCliDeserialize($nmCli);
		if ($type === WifiSecurityType::OPEN) {
			return new self($type, null, null, null, null);
		}
		$leap = Leap::nmCliDeserialize($nmCli);
		assert($leap instanceof Leap);
		$wep = Wep::nmCliDeserialize($nmCli);
		assert($wep instanceof Wep);
		if ($type === WifiSecurityType::WPA_EAP) {
			$eap = Eap::nmCliDeserialize($nmCli);
			assert($eap instanceof Eap);
		}
		return new self($type, $array['psk'], $leap, $wep, $eap ?? null);
	}

	/**
	 * Serializes WiFi connection security entity into JSON
	 * @return array{type: string, psk: string|null, leap?: array{username: string, password: string}, wep?: array{type: string, index: int, keys: array<string>}, eap?: array{phaseOneMethod: string|null, phaseTwoMethod: string|null, anonymousIdentity: string, cert: string, identity: string, password: string}} JSON serialized entity
	 */
	public function jsonSerialize(): array {
		$array = [
			'type' => $this->type->value,
			'psk' => $this->psk,
		];
		if ($this->leap instanceof Leap) {
			$array['leap'] = $this->leap->jsonSerialize();
		}
		if ($this->wep instanceof Wep) {
			$array['wep'] = $this->wep->jsonSerialize();
		}
		if ($this->eap instanceof Eap) {
			$array['eap'] = $this->eap->jsonSerialize();
		}
		return $array;
	}

	/**
	 * Serializes WiFi connection security entity into nmcli configuration string
	 * @return string nmcli configuration
	 */
	public function nmCliSerialize(): string {
		$config = $this->type->nmCliSerialize();
		if ($this->type === WifiSecurityType::OPEN) {
			return $config;
		}
		$array = [
			'psk' => $this->psk,
		];
		$config .= NmCliConnection::encode($array, self::NMCLI_PREFIX);
		if ($this->leap instanceof Leap) {
			$config .= $this->leap->nmCliSerialize();
		}
		if ($this->wep instanceof Wep) {
			$config .= $this->wep->nmCliSerialize();
		}
		if ($this->type === WifiSecurityType::WPA_EAP) {
			$config .= $this->eap->nmCliSerialize();
		}
		return $config;
	}

}
