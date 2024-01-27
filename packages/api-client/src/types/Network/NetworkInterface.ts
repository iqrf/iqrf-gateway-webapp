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

/**
 * Network interface states
 */
export enum NetworkInterfaceState {
	/// The interface is checking whether further action is required for the requested network connection
	CheckingIpConnectivity = 'connecting (checking IP connectivity)',
	/// The interface is configuring connection to the requested network
	Configuring = 'connecting (configuring)',
	/// The interface has a network connection
	Connected = 'connected',
	/// The interface is disconnecting from the current network and the interface is cleaning up resources used for that connection
	Deactivating = 'deactivating',
	/// The interface can be activated, but is currently idle and not connected to the network
	Disconnected = 'disconnected',
	/// The interface failed to connect to the requested network
	Failed = 'failed',
	/// The interface is getting IP configuration
	GetingIpConfiguration = 'connecting (getting IP configuration)',
	/// The interface requires more information to continue connecting to the requested network
	NeedAuthentication = 'connecting (need authentication)',
	/// The interface is preparing the connection to the network
	Prepare = 'connecting (prepare)',
	/// The interface is waiting for a secondary connection (like a VPN) which must activated before the interface can be activated
	StartingSecondaries = 'connecting (starting secondary connections)',
	/// The interface is managed by NetworkManager, but is not available for use
	Unavailable = 'unavailable',
	/// The interface's state is unknown
	Unknown = 'unknown',
	/// The interface is recognized, but not managed by NetworkManager
	Unmanaged = 'unmanaged',
}

/**
 * Network interface types
 */
export enum NetworkInterfaceType {
	/// Bluetooth interface
	BLUETOOTH = 'bt',
	/// Bond interface
	BOND = 'bond',
	/// Bridge interface
	BRIDGE = 'bridge',
	/// Dummy interface
	DUMMY = 'dummy',
	/// Ethernet interface
	ETHERNET = 'ethernet',
	/// GSM interface
	GSM = 'gsm',
	/// IP tunnel interface
	IP_TUNNEL = 'iptunnel',
	/// Loopback interface
	LOOPBACK = 'loopback',
	/// PPP interface
	PPP = 'ppp',
	/// TUN interface
	TUN = 'tun',
	/// VLAN interface
	VLAN = 'vlan',
	/// Wi-Fi interface
	WIFI = 'wifi',
	/// Wi-Fi P2P interface
	WIFI_P2P = 'wifi-p2p',
	/// WireGuard interface
	WIREGUARD = 'wireguard',
}

/**
 * Network interface
 */
export interface NetworkInterface {
	/// Active connection UUID
	connection: string;
	/// Interface MAC address
	macAddress: string|null;
	/// Interface manufacturer
	manufacturer: string|null;
	/// Interface model
	model: string|null;
	/// Interface name
	name: string;
	/// Interface state
	state: NetworkInterfaceState;
	/// Interface type
	type: string;
}
