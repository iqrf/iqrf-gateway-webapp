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

import {WireguardStack} from '@/enums/Network/Wireguard';

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
	stack?: WireguardStack
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
	stack?: WireguardStack
}

/**
 * Wireguard VPN peer allowed IP interface
 */
export interface IWGAllowedIP {
	address: string
	prefix: number
}
