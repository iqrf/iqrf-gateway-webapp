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

namespace App\NetworkModule\Enums;

/**
 * Interface type enum
 */
enum InterfaceTypes: string {

	/// Bond master interface
	case BOND = 'bond';
	/// Bluetooth interface
	case BLUETOOTH = 'bt';
	/// Bridge master interface
	case BRIDGE = 'bridge';
	/// Dummy interface
	case DUMMY = 'dummy';
	/// Wired Ethernet interface
	case ETHERNET = 'ethernet';
	/// GSM interface
	case GSM = 'gsm';
	/// IP tunnel interface
	case IP_TUNNEL = 'iptunnel';
	/// Loopback interface
	case LOOPBACK = 'loopback';
	/// Point to Point interface
	case PPP = 'ppp';
	/// TUN or TAP interface
	case TUN = 'tun';
	/// 802.1Q VLAN interface
	case VLAN = 'vlan';
	/// 802.11 WiFi interface
	case WIFI = 'wifi';
	/// WiFi P2P interface
	case WIFI_P2P = 'wifi-p2p';
	/// WireGuard
	case WIREGUARD = 'wireguard';

}
