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

import {ConnectionType} from '@/enums/Network/ConnectionType';
import {Ipv4Method, Ipv6Method} from '@/enums/Network/Ip';
import {IWifiSecurity} from './Wifi';
import {InterfaceState} from '@/enums/Network/InterfaceState';

/**
 * Network connection interface
 */
export interface NetworkConnection {
	interfaceName: string | null
	name: string
	type: string
	uuid: string
	bssid?: Array<string>
}

/**
 * Network interface interface
 */
export interface NetworkInterface {
	connectionName: string
	macAddress: string|null
	manufacturer: string|null
	model: string|null
	name: string
	state: InterfaceState
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
	type?: ConnectionType|null
	interface?: string
	ipv4: IConnectionIPv4
	ipv6: IConnectionIPv6
	wifi?: IConnectionWifi
	gsm?: IConnectionGSM
	serial?: IConnectionSerial
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
	method: Ipv4Method
	current?: IConnectionIPv4
}

export interface IConnectionIPv6 {
	addresses: Array<IConnectionIPv6Addrs>
	dns: Array<IConnectionIPDns>
	gateway: string|null
	method: Ipv6Method
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

export interface IConnectionGSM {
	apn: string
	username: string
	password: string
	pin: string
}

export interface IConnectionSerial {
	baudRate: number
	bits: number
	parity: ''|'E'|'o'|'n'
	sendDelay: number
	stopBits: 1|2
}

export interface IConnectionWifi {
	ssid: string
	mode: string
	bssids?: Array<string>
	security: IWifiSecurity
}
