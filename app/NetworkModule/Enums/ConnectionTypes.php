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

namespace App\NetworkModule\Enums;

/**
 * Network connection type enum
 */
enum ConnectionTypes: string {

	/// Bluetooth connection
	case BLUETOOTH = 'bluetooth';
	/// Bridge connection
	case BRIDGE = 'bridge';
	/// Dummy connection
	case DUMMY = 'dummy';
	/// Ethernet connection
	case ETHERNET = '802-3-ethernet';
	/// GSM connection
	case GSM = 'gsm';
	/// IP-over-InfiniBand connection
	case INFINIBAND = 'infiniband';
	/// IP tunnel connection
	case IP_TUNNEL = 'ip-tunnel';
	/// Loopback connection
	case LOOPBACK = 'loopback';
	/// TUN connection
	case TUN = 'tun';
	/// VLAN connection
	case VLAN = 'vlan';
	/// VPN connection
	case VPN = 'vpn';
	/// WiFi connection
	case WIFI = '802-11-wireless';
	/// WiMAX connection
	case WIMAX = 'wimax';
	/// WireGuard connection
	case WIREGUARD = 'wireguard';
	/// WPAN (IEEE 802.15.4) connection
	case WPAN = 'wpan';

	/**
	 * Serializes network connection type enum into JSON string
	 * @return string JSON serialized string
	 */
	public function jsonSerialize(): string {
		return $this->value;
	}

}
