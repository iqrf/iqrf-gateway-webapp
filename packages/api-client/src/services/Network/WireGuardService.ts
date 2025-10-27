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

import { type AxiosResponse } from 'axios';

import {
	WireGuardIpStack,
	type WireGuardKeyPair,
	type WireGuardPeer,
	type WireGuardTunnelConfig,
	type WireGuardTunnelListEntry,
} from '../../types/Network';
import { BaseService } from '../BaseService';

/**
 * WireGuard service
 */
export class WireGuardService extends BaseService {

	/**
	 * Generates a new WireGuard key-pair
	 * @return {Promise<WireGuardKeyPair>} WireGuard key-pair
	 */
	public async generateKeyPair(): Promise<WireGuardKeyPair> {
		const response: AxiosResponse<WireGuardKeyPair> =
			await this.axiosInstance.post('/network/wireguard/keypair');
		return response.data;
	}

	/**
	 * Retrieves a list of existing WireGuard tunnels
	 * @return {Promise<WireGuardTunnelListEntry[]>} List of existing WireGuard tunnels
	 */
	public async listTunnels(): Promise<WireGuardTunnelListEntry[]> {
		const response: AxiosResponse<WireGuardTunnelListEntry[]> =
			await this.axiosInstance.get('/network/wireguard');
		return response.data;
	}

	/**
	 * Creates a new WireGuard tunnel
	 * @param {WireGuardTunnelConfig} config WireGuard tunnel configuration
	 */
	public async createTunnel(config: WireGuardTunnelConfig): Promise<void> {
		await this.axiosInstance.post('/network/wireguard', this.serializeTunnel(config));
	}

	/**
	 * Updates an existing WireGuard tunnel
	 * @param {number} id WireGuard tunnel ID
	 * @param {WireGuardTunnelConfig} config WireGuard tunnel configuration
	 */
	public async updateTunnel(id: number, config: WireGuardTunnelConfig): Promise<void> {
		await this.axiosInstance.put(`/network/wireguard/${id.toString()}`, this.serializeTunnel(config));
	}

	/**
	 * Deletes an existing WireGuard tunnel
	 * @param {number} id WireGuard tunnel ID
	 */
	public async deleteTunnel(id: number): Promise<void> {
		await this.axiosInstance.delete(`/network/wireguard/${id.toString()}`);
	}

	/**
	 * Retrieves an existing WireGuard tunnel configuration
	 * @param {number} id WireGuard tunnel ID
	 * @return {Promise<WireGuardTunnelConfig>} WireGuard tunnel configuration
	 */
	public async getTunnel(id: number): Promise<WireGuardTunnelConfig> {
		const response: AxiosResponse<WireGuardTunnelConfig> =
			await this.axiosInstance.get(`/network/wireguard/${id.toString()}`);
		return this.deserializeTunnel(response.data);
	}

	/**
	 * Activates an existing WireGuard tunnel
	 * @param {number} id WireGuard tunnel ID
	 */
	public async activateTunnel(id: number): Promise<void> {
		await this.axiosInstance.post(`/network/wireguard/${id.toString()}/activate`);
	}

	/**
	 * Deactivates an existing WireGuard tunnel
	 * @param {number} id WireGuard tunnel ID
	 */
	public async deactivateTunnel(id: number): Promise<void> {
		await this.axiosInstance.post(`/network/wireguard/${id.toString()}/deactivate`);
	}

	/**
	 * Enables an existing WireGuard tunnel
	 * @param {number} id WireGuard tunnel ID
	 */
	public async enableTunnel(id: number): Promise<void> {
		await this.axiosInstance.post(`/network/wireguard/${id.toString()}/enable`);
	}

	/**
	 * Disables an existing WireGuard tunnel
	 * @param {number} id WireGuard tunnel ID
	 */
	public async disableTunnel(id: number): Promise<void> {
		await this.axiosInstance.post(`/network/wireguard/${id.toString()}/disable`);
	}

	/**
	 * Deserializes WireGuard tunnel configuration
	 * @param {WireGuardTunnelConfig} tunnel WireGuard tunnel configuration
	 * @return {WireGuardTunnelConfig} Deserialized WireGuard tunnel configuration
	 */
	private deserializeTunnel(tunnel: WireGuardTunnelConfig): WireGuardTunnelConfig {
		if (tunnel.ipv4 === undefined) {
			tunnel.stack = WireGuardIpStack.IPV6;
			tunnel.ipv4 = { address: '', prefix: 24 };
		} else if (tunnel.ipv6 === undefined) {
			tunnel.stack = WireGuardIpStack.IPV4;
			tunnel.ipv6 = { address: '', prefix: 64 };
		} else {
			tunnel.stack = WireGuardIpStack.DUAL;
		}
		for (const [idx, value] of tunnel.peers.entries()) {
			tunnel.peers[idx] = this.deserializePeer(value);
		}
		return tunnel;
	}

	/**
	 * Serializes WireGuard tunnel configuration
	 * @param {WireGuardTunnelConfig} tunnel WireGuard tunnel configuration
	 * @return {WireGuardTunnelConfig} Serialized WireGuard tunnel configuration
	 */
	private serializeTunnel(tunnel: WireGuardTunnelConfig): WireGuardTunnelConfig {
		switch (tunnel.stack) {
			case WireGuardIpStack.IPV4:
				delete tunnel.ipv6;
				break;
			case WireGuardIpStack.IPV6:
				delete tunnel.ipv4;
				break;
		}
		delete tunnel.stack;
		for (const [idx, value] of tunnel.peers.entries()) {
			tunnel.peers[idx] = this.serializePeer(value);
		}
		delete tunnel.publicKey;
		return tunnel;
	}

	/**
	 * Deserializes WireGuard peer configuration
	 * @param {WireGuardPeer} peer WireGuard peer configuration
	 * @return {WireGuardPeer} Deserialized WireGuard peer configuration
	 */
	private deserializePeer(peer: WireGuardPeer): WireGuardPeer {
		if (peer.allowedIPs.ipv4.length === 0) {
			peer.allowedIPs.ipv4.push({ address: '', prefix: 24 });
			peer.allowedIPs.stack = WireGuardIpStack.IPV6;
		} else if (peer.allowedIPs.ipv6.length === 0) {
			peer.allowedIPs.ipv6.push({ address: '', prefix: 64 });
			peer.allowedIPs.stack = WireGuardIpStack.IPV4;
		} else {
			peer.allowedIPs.stack = WireGuardIpStack.DUAL;
		}
		return peer;
	}

	/**
	 * Serializes WireGuard peer configuration
	 * @param {WireGuardPeer} peer WireGuard peer configuration
	 * @return {WireGuardPeer} Serialized WireGuard peer configuration
	 */
	private serializePeer(peer: WireGuardPeer): WireGuardPeer {
		if (peer.psk === '' || peer.psk === undefined) {
			delete peer.psk;
		}
		if (peer.allowedIPs.stack === WireGuardIpStack.IPV4) {
			peer.allowedIPs.ipv6 = [];
		} else if (peer.allowedIPs.stack === WireGuardIpStack.IPV6) {
			peer.allowedIPs.ipv4 = [];
		}
		delete peer.allowedIPs.stack;
		return peer;
	}

}
