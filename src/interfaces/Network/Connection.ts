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
import {IWifiSecurity} from './Wifi';

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



export interface IConnectionGSM {
	apn: string
	username: string
	password: string
	pin: string
}

export interface IConnectionWifi {
	ssid: string
	mode: string
	bssids?: Array<string>
	security: IWifiSecurity
}