/**
 * Copyright 2023-2024 MICRORISC s.r.o.
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

import { type WifiSecurity } from './Wifi';

/**
 * Network connection states
 */
export enum NetworkConnectionState {
	/// There is a connection to the network
	Activated = 'activated',
	/// A network connection is being prepared
	Activating = 'activating',
	/// The network connection is disconnected and will be removed
	Deactivated = 'deactivated',
	/// The network connection is being torn down and cleaned up
	Deactivating = 'deactivating',
	/// The state of the connection is unknown
	Unknown = 'unknown',
}

/**
 * Auto-connect configuration
 */
export interface AutoConnectConfiguration {
	/// Auto-connect enabled
	enabled: boolean;
	/// Auto-connect priority
	priority: number;
	/// Auto-connect retries,
	retries: number;
}

/**
 * DNS server configuration
 */
export interface DnsServerConfiguration {
	/// DNS server address
	address: string;
}

/**
 * IPv4 address
 */
export interface IPv4Address {
	/// IPv4 address
	address: string;
	/// IPv4 network mask
	mask: string;
	/// IPv4 network prefix
	prefix?: number;
}

/**
 * IPv4 configuration method
 */
export enum IPV4ConfigurationMethod {
	/// DHCPv4
	AUTO = 'auto',
	/// Disabled
	DISABLED = 'disabled',
	/// Link-local
	LINK_LOCAL = 'link-local',
	/// Manual configuration
	MANUAL = 'manual',
	/// Shared with other computers
	SHARED = 'shared',
}


/**
 * IPv4 configuration
 */
export interface IPv4Configuration {
	/// IPv4 addresses
	addresses: IPv4Address[];
	/// Current IPv4 configuration
	current?: IPv4Configuration;
	/// IPv4 DNS servers
	dns: DnsServerConfiguration[];
	/// IPv4 gateway address
	gateway: string|null;
	/// IPv4 configuration method
	method: IPV4ConfigurationMethod;
}

/**
 * IPv6 address
 */
export interface IPv6Address {
	/// IPv6 address
	address: string;
	/// IPv6 network prefix
	prefix: number;
}

/**
 * IPv6 configuration method
 */
export enum IPV6ConfigurationMethod {
	/// SLAAC
	AUTO = 'auto',
	/// DHCPv6 only
	DHCP = 'dhcp',
	/// Disabled
	DISABLED = 'disabled',
	/// Ignore
	IGNORE = 'ignore',
	/// Link-local
	LINK_LOCAL = 'link-local',
	/// Manual configuration
	MANUAL = 'manual',
	/// Shared with other computers
	SHARED = 'shared',
}

/**
 * IPv6 configuration
 */
export interface IPv6Configuration {
	/// IPv6 addresses
	addresses: IPv6Address[];
	/// Current IPv6 configuration
	current?: IPv6Configuration;
	/// IPv6 DNS servers
	dns: DnsServerConfiguration[];
	/// IPv6 gateway address
	gateway: string|null;
	/// IPv6 configuration method
	method: IPV6ConfigurationMethod;
}

/**
 * Mobile connection
 */
export interface MobileConnection {
	/// Mobile connection APN
	apn: string;
	/// Mobile connection password
	password: string;
	/// SIM card PIN
	pin: string;
	/// Mobile connection username
	username: string;
}

/**
 * Serial connection
 */
export interface SerialConnection {
	/// Serial port baud rate
	baudRate: number
	/// Serial port bits
	bits: number
	/// Serial port parity
	parity: ''|'E'|'o'|'n'
	/// Serial port send delay
	sendDelay: number
	/// Serial port stop bits
	stopBits: 1|2
}

/**
 * VLAN connection flags
 */
export interface VlanFlags {
	/// This interface should use GVRP to register itself with its switch.
	gvrp: boolean,
	/// This interface's operating state is tied to the underlying network interface but other details (like routing) are not.
	looseBinding: boolean,
	/// This interface should use MVRP to register itself with its switch.
	mvrp: boolean,
	/// This interface should reorder outgoing packet headers to look more like a non-VLAN Ethernet interface.
	reorderHeaders: boolean
}

/**
 * VLAN connection
 */
export interface VlanConnection {
	/// VLAN flags
	flags: VlanFlags,
	/// VLAN ID
	id: number,
	/// Parent Ethernet interface name
	parentInterface: string
}

/**
 * WiFi connection
 */
export interface WifiConnection {
	/// BSSIDs
	bssids?: string[];
	/// WiFi mode
	mode: string;
	/// WiFi security
	security: WifiSecurity;
	/// SSID
	ssid: string;
}

/**
 * Network connection types
 */
export enum NetworkConnectionType {
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
	/// WPAN (IEEE 802.15.4) connection
	WPAN = 'wpan',
	/// Wi-Fi connection
	WiFi = '802-11-wireless',
	/// WiMAX connection
	WiMAX = 'wimax',
	/// WireGuard connection
	WireGuard = 'wireguard',
}

/**
 * Network connection configuration
 */
export interface NetworkConnectionConfiguration {
	/// Auto-connect configuration
	autoConnect: AutoConnectConfiguration;
	/// Mobile connection
	gsm?: MobileConnection;
	/// Network interface name
	interface?: string;
	/// IPv4 configuration
	ipv4: IPv4Configuration;
	/// IPv6 configuration
	ipv6: IPv6Configuration;
	/// Network connection name
	name: string;
	/// Serial connection
	serial?: SerialConnection;
	/// Network connection type
	type?: NetworkConnectionType|null;
	/// Network connection UUID
	uuid?: string;
	/// VLAN connection
	vlan?: VlanConnection;
	/// WiFi connection
	wifi?: WifiConnection;
}

/**
 * Network connection list entry
 */
export interface NetworkConnectionListEntry {
	/// WiFi BSSIDs
	bssids?: string[];
	/// Interface name
	interfaceName: string|null;
	/// Network connection name
	name: string;
	/// Network connection type
	type: NetworkConnectionType;
	/// Network connection UUID
	uuid: string;
}
