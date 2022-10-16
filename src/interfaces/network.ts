/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
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

import {ConfigurationMethod} from '@/enums/Network/Ip';
import {EapPhaseOneMethod, EapPhaseTwoMethod} from '@/enums/Network/WifiSecurity';

/**
 * Network connection interface
 */
export interface NetworkConnection {
	interfaceName: string
	name: string
	type: string
	uuid: string
	bssid?: Array<string>
}

/**
 * Network interface interace
 */
export interface NetworkInterface {
	connectionName: string
	name: string
	state: string
	type: string
}

/**
 * Connection modal interface
 */
export interface IConnectionModal {
	ipv4: string|null
	ipv4Addr: string|null
}

export interface IConnection {
	autoConnect: IConnectionAutoConnect
	name: string
	uuid?: string
	type?: string
	interface?: string
	ipv4: IConnectionIPv4
	ipv6: IConnectionIPv6
	wifi?: IConnectionWifi
	gsm?: IConnectionGSM
}

export interface IConnectionAutoConnect {
	enabled: boolean,
	priority: number,
	retries: number
}

export interface IConnectionCurrent {
	ipv4: IConnectionIPv4
	ipv6: IConnectionIPv6
}

export interface IConnectionIPv4 {
	addresses: Array<IConnectionIPv4Addrs>
	dns: Array<IConnectionIPDns>
	gateway: string|null
	method: ConfigurationMethod
	current?: IConnectionIPv4
}

export interface IConnectionIPv6 {
	addresses: Array<IConnectionIPv6Addrs>
	dns: Array<IConnectionIPDns>
	gateway: string|null
	method: ConfigurationMethod
	current?: IConnectionIPv6
}

export interface IConnectionIPv4Addrs {
	address: string
	mask: string
	prefix?: number
}

export interface IConnectionIPv6Addrs {
	address: string
	prefix: number
}

export interface IConnectionIPDns {
	address: string
}

export interface IConnectionWifi {
	ssid: string
	mode: string
	bssids?: Array<string>
	security: IWifiSecurity
}

export interface IConnectionGSM {
	apn: string
	username: string
	password: string
	pin: string
}

export interface IAccessPointArray {
	ssid: string,
	aps: Array<IAccessPoint>
}

export interface IAccessPoint {
	inUse: boolean
	bssid: string
	ssid: string
	mode: string
	channel: number
	rate: string
	signal: number
	security: string
	uuid?: string
	interfaceName: string|null
}

export interface IWifiSecurity {
	type: string
	psk: string
	leap: IWifiLeap
	wep: IWifiWep
	eap: IWifiEap
}

/**
 * EAP (Extensible Authentication Protocol) interface
 */
export interface IWifiEap {
	phaseOneMethod: EapPhaseOneMethod|null
	phaseTwoMethod: EapPhaseTwoMethod|null
	anonymousIdentity: string
	cert: string
	identity: string
	password: string
}

/**
 * Cisco LEAP security interface
 */
export interface IWifiLeap {
	username: string
	password: string
}

/**
 * WEP security interface
 */
export interface IWifiWep {
	type: string
	index: number
	keys: Array<string>
}

/**
 * Wireguard VPN list entry interface
 */
export interface IWG {
	id: number
	name: string
	active: boolean
	enabled: boolean
}

/**
 * Wireguard VPN tunnel interface
 */
export interface IWGTunnel {
	name: string
	privateKey: string
	publicKey?: string
	port?: number
	ipv4?: IWGTunnelIP
	ipv6?: IWGTunnelIP
	peers: Array<IWGPeer>
}

/**
 * Wireguard VPN tunnel interface address
 */
export interface IWGTunnelIP {
	address: string
	prefix: number
}

/**
 * Wireguard VPN peer interface
 */
export interface IWGPeer {
	publicKey: string,
	psk?: string
	keepalive: number
	endpoint: string
	port: number
	allowedIPs: IWGAllowedIPs
}

/**
 * Wireguard VPN peer allowed IPs interface
 */
export interface IWGAllowedIPs {
	ipv4: Array<IWGAllowedIP>
	ipv6: Array<IWGAllowedIP>
}

/**
 * Wireguard VPN peer allowed IP interface
 */
export interface IWGAllowedIP {
	address: string
	prefix: number
}

/**
 * Modem interface
 */
export interface IModem {
	/**
	 * Interface name
	 */
	interface: string

	/**
	 * Signal strength
	 */
	signal: number

	/**
	 * RSSI
	 */
	rssi: number
}

/**
 * Network operator interface
 */
export interface IOperator {
	/**
	 * Operator ID
	 */
	id?: number

	/**
	 * Operator name
	 */
	name: string

	/**
	 * Operator APN
	 */
	apn: string

	/**
	 * APN access username
	 */
	username?: string

	/**
	 * APN access password
	 */
	password?: string
}
