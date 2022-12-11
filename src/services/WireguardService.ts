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
import axios, {AxiosResponse} from 'axios';
import {authorizationHeader} from '@/helpers/authorizationHeader';

import {IWGTunnel} from '@/interfaces/Network/Wireguard';
import {WireguardStack} from '@/enums/Network/Wireguard';

/**
 * Wireguard VPN service
 */
class WireguardService {

	/**
	 * Creates a new Wireguard key-pair
	 */
	createKeys(): Promise<AxiosResponse> {
		return axios.post('network/wireguard/keypair', null, {headers: authorizationHeader()});
	}

	/**
	 * Creates a new Wireguard tunnel
	 * @param {IWGTunnel} data Wireguard tunnel configuration
	 */
	createTunnel(data: IWGTunnel): Promise<AxiosResponse> {
		return axios.post('network/wireguard', data, {headers: authorizationHeader()});
	}

	/**
	 * Edits an existing Wireguard tunnel
	 * @param id Wireguard tunnel ID
	 * @param data Wireguard tunnel configuration
	 */
	editTunnel(id: number, data: IWGTunnel): Promise<AxiosResponse> {
		return axios.put('network/wireguard/' + id, data, {headers: authorizationHeader()});
	}

	/**
	 * Retrieves list of existing Wireguard tunnel configurations
	 */
	listTunnels(): Promise<AxiosResponse> {
		return axios.get('network/wireguard', {headers: authorizationHeader()});
	}

	/**
	 * Retrieves configuration of existing Wireguard tunnel
	 * @param {number} id Wireguard tunnel name
	 */
	getTunnel(id: number): Promise<IWGTunnel> {
		return axios.get('network/wireguard/' + id, {headers: authorizationHeader()})
			.then((response: AxiosResponse) => {
				const tunnel = response.data as IWGTunnel;
				for (const idx in tunnel.peers) {
					if (tunnel.peers[idx].allowedIPs.ipv4.length === 0) {
						tunnel.peers[idx].allowedIPs.ipv4.push({address: '', prefix: 24});
						tunnel.peers[idx].allowedIPs.stack = WireguardStack.IPV4;
					} else if (tunnel.peers[idx].allowedIPs.ipv6.length === 0) {
						tunnel.peers[idx].allowedIPs.ipv6.push({address: '', prefix: 64});
						tunnel.peers[idx].allowedIPs.stack = WireguardStack.IPV6;
					} else {
						tunnel.peers[idx].allowedIPs.stack = WireguardStack.DUAL;
					}
				}
				if (tunnel.ipv4 !== undefined && tunnel.ipv6 !== undefined) {
					tunnel.stack = WireguardStack.DUAL;
				} else if (tunnel.ipv4 !== undefined && tunnel.ipv6 === undefined) {
					tunnel.stack = WireguardStack.IPV4;
					Object.assign(tunnel, {ipv6: {address: '', prefix: 64}});
				} else {
					tunnel.stack = WireguardStack.IPV6;
					Object.assign(tunnel, {ipv4: {address: '', prefix: 24}});
				}
				return tunnel;
			});
	}

	/**
	 * Removes an existing Wireguard tunnel
	 * @param {number} id Wireguard tunnel name
	 */
	removeTunnel(id: number): Promise<AxiosResponse> {
		return axios.delete('network/wireguard/' + id, {headers: authorizationHeader()});
	}

	/**
	 * Activates a Wireguard tunnel
	 * @param id Wireguard tunnel ID
	 */
	activateTunnel(id: number): Promise<AxiosResponse> {
		return axios.post('network/wireguard/' + id + '/activate', null, {headers: authorizationHeader()});
	}

	/**
	 * Deactivates a Wireguard tunnel
	 * @param id Wireguard tunnel ID
	 */
	deactivateTunnel(id: number): Promise<AxiosResponse> {
		return axios.post('network/wireguard/' + id + '/deactivate', null, {headers: authorizationHeader()});
	}

	/**
	 * Enables a Wireguard tunnel autostart
	 * @param id Wireguard tunnel ID
	 */
	enableTunnel(id: number): Promise<AxiosResponse> {
		return axios.post('network/wireguard/' + id + '/enable', null, {headers: authorizationHeader()});
	}

	/**
	 * Disables a Wireguard tunnel autostart
	 * @param id Wireguard tunnel ID
	 */
	disableTunnel(id: number): Promise<AxiosResponse> {
		return axios.post('network/wireguard/' + id + '/disable', null, {headers: authorizationHeader()});
	}

}

export default new WireguardService();
