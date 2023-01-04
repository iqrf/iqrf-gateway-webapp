/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
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

/**
 * Network connection types
 */
export enum ConnectionType {
	/// Bluetooth connection
	Bluetooth = 'bluetooth',
	/// Bridge connection
	Bridge = 'bridge',
	/// Dummy connection
	Dummy = 'dummy',
	/// Ethernet connection
	Ethernet = '802-3-ethernet',
	/// GSM connection
	GSM = 'gsm',
	/// IP-over-InfiniBand connection
	InfiniBand = 'infiniband',
	/// IP tunnel connection
	IpTunnel = 'ip-tunnel',
	/// TUN connection
	TUN = 'tun',
	/// VLAN connection
	VLAN = 'vlan',
	/// VPN connection
	VPN = 'vpn',
	/// Wi-Fi connection
	WiFi = '802-11-wireless',
	/// WiMAX connection
	WiMAX = 'wimax',
	/// WireGuard connection
	WireGuard = 'wireguard',
	/// WPAN (IEEE 802.15.4) connection
	WPAN = 'wpan',
}
