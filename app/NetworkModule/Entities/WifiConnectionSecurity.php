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

use App\NetworkModule\Entities\WifiSecurity\Eap;
use App\NetworkModule\Entities\WifiSecurity\Leap;
use App\NetworkModule\Entities\WifiSecurity\Wep;
use App\NetworkModule\Enums\WifiSecurityType;
use App\NetworkModule\Utils\NmCliConnection;
use stdClass;

/**
 * WiFi connection security entity
 */
final class WifiConnectionSecurity implements INetworkManagerEntity {

	/**
	 * nmcli 802-11-wireless security configuration prefix
	 */
	public const NMCLI_PREFIX = '802-11-wireless-security';

	/**
	 * nmcli 802-1x security configuration prefix
	 */
	public const NMCLI_EAP_PREFIX = '802-1x';

	/**
	 * @var WifiSecurityType WiFi security type
	 */
	private $type;

	/**
	 * @var string|null Pre-shared key
	 */
	private $psk;

	/**
	 * @var Leap|null Cisco LEAP entity
	 */
	private $leap;

	/**
	 * @var Wep|null WEP entity
	 */
	private $wep;

	/**
	 * @var Eap|null EAP entity
	 */
	private $eap;

	/**
	 * Constructor
	 * @param WifiSecurityType $type WiFi security type
	 * @param string|null $psk Pre-shared key
	 * @param Leap|null $leap Cisco LEAP entity
	 * @param Wep|null $wep WEP entity
	 */
	public function __construct(WifiSecurityType $type, ?string $psk, ?Leap $leap, ?Wep $wep, ?Eap $eap) {
		$this->type = $type;
		$this->psk = $psk;
		$this->leap = $leap;
		$this->wep = $wep;
		$this->eap = $eap;
	}

	/**
	 * Deserializes WiFi connection security entity from JSON
	 * @param stdClass $json JSON serialized entity
	 * @return WifiConnectionSecurity WiFI connection security entity
	 */
	public static function jsonDeserialize(stdClass $json): INetworkManagerEntity {
		$type = WifiSecurityType::fromScalar($json->type);
		$leap = Leap::jsonDeserialize($json->leap);
		assert($leap instanceof Leap);
		$wep = Wep::jsonDeserialize($json->wep);
		assert($wep instanceof Wep);
		if ($type === WifiSecurityType::WPA_EAP()) {
			$eap = Eap::jsonDeserialize($json->eap);
			assert($eap instanceof Eap);
		}
		return new self($type, $json->psk, $leap, $wep, $eap ?? null);
	}


	/**
	 * Serializes WiFi connection security entity into JSON
	 * @return array<string, string|array> JSON serialized entity
	 */
	public function jsonSerialize(): array {
		$array = [
			'type' => $this->type->toScalar(),
			'psk' => $this->psk,
		];
		if ($this->leap !== null) {
			$array['leap'] = $this->leap->jsonSerialize();
		}
		if ($this->wep !== null) {
			$array['wep'] = $this->wep->jsonSerialize();
		}
		if ($this->eap !== null) {
			$array['eap'] = $this->eap->jsonSerialize();
		}
		return $array;
	}

	/**
	 * Deserializes WiFi connection security entity from nmcli connection configuration
	 * @param string $nmCli nmcli connection configuration
	 * @return WifiConnectionSecurity WiFI connection security entity
	 */
	public static function nmCliDeserialize(string $nmCli): INetworkManagerEntity {
		$array = NmCliConnection::decode($nmCli, self::NMCLI_PREFIX);
		$type = WifiSecurityType::nmCliDeserialize($nmCli);
		if ($type->equals(WifiSecurityType::OPEN())) {
			return new self($type, null, null, null, null);
		}
		$leap = Leap::nmCliDeserialize($nmCli);
		assert($leap instanceof Leap);
		$wep = Wep::nmCliDeserialize($nmCli);
		assert($wep instanceof Wep);
		if ($type === WifiSecurityType::WPA_EAP()) {
			$eap = Eap::nmCliDeserialize($nmCli);
			assert($eap instanceof Eap);
		}
		return new self($type, $array['psk'], $leap, $wep, $eap ?? null);
	}

	/**
	 * Serializes WiFi connection security entity into nmcli configuration string
	 * @return string nmcli configuration
	 */
	public function nmCliSerialize(): string {
		$config = $this->type->nmCliSerialize();
		if ($this->type->equals(WifiSecurityType::OPEN())) {
			return $config;
		}
		$array = [
			'psk' => $this->psk,
		];
		$config .= NmCliConnection::encode($array, self::NMCLI_PREFIX);
		$config .= $this->leap->nmCliSerialize();
		$config .= $this->wep->nmCliSerialize();
		if ($this->type === WifiSecurityType::WPA_EAP()) {
			$config .= $this->eap->nmCliSerialize();
		}
		return $config;
	}

}
