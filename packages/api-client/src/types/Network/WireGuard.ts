/**
 * Copyright 2023-2025 MICRORISC s.r.o.
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
 * WireGuard key-pair
 */
export interface WireGuardKeyPair {
	/// Private key
	privateKey: string;
	/// Public key
	publicKey: string;
}

/**
 * WireGuard IP stack
 */
export enum WireGuardIpStack {
	/// Dual-stack
	DUAL = 'both',
	/// IPv4-only
	IPV4 = 'ipv4',
	/// IPv6-only
	IPV6 = 'ipv6',
}

/**
 * WireGuard single IP stack
 */
export type WireGuardIpStackSingle = Exclude<WireGuardIpStack, WireGuardIpStack.DUAL>;

/**
 * WireGuard peer allowed IP
 */
export interface WireGuardIpAddress {
	/// Record ID
	id?: number;
	/// IP address
	address: string;
	/// Prefix length
	prefix: number;
}

/**
 * WireGuard peer allowed IPs
 */
export interface WireGuardAllowedIps {
	/// IPv4 allowed IPs
	ipv4: WireGuardIpAddress[];
	/// IPv6 allowed IPs
	ipv6: WireGuardIpAddress[];
}

/**
 * WireGuard peer
 */
export interface WireGuardPeer {
	/// Record ID
	id?: number;
	/// Peer public key
	publicKey: string;
	/// Pre-shared key
	psk?: string;
	/// Keepalive interval
	keepalive: number;
	/// Endpoint
	endpoint: string;
	/// Endpoint port
	port: number;
	/// Allowed IPs
	allowedIPs: WireGuardAllowedIps;
	/// Tunnel ID
	tunnelId: number;
}

/**
 * WireGuard tunnel configuration
 */
export interface WireGuardTunnelConfig {
	/// Record ID
	id?: number;
	/// Tunnel name
	name: string;
	/// Tunnel private key
	privateKey?: string;
	/// Tunnel public key
	publicKey?: string;
	/// Tunnel port
	port?: number;
	/// Tunnel IPv4 address
	ipv4?: WireGuardIpAddress;
	/// Tunnel IPv6 address
	ipv6?: WireGuardIpAddress;
	// Tunnel IP stack
	stack?: WireGuardIpStack;
}

/**
 * WireGuard tunnel list entry
 */
export interface WireGuardTunnelListEntry {
	/// Tunnel active status
	active: boolean;
	/// Tunnel enabled status
	enabled: boolean;
	/// Tunnel ID
	id: number;
	/// Tunnel name
	name: string;
	// Tunnel IP stack
	stack?: WireGuardIpStack;
}
