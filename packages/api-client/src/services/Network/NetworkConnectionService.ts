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

import { type AxiosRequestConfig, type AxiosResponse } from 'axios';

import {
	type NetworkConnectionConfiguration,
	type NetworkConnectionListEntry,
	type NetworkConnectionType,
} from '../../types/Network/NetworkConnection';
import { BaseService } from '../BaseService';

/**
 * Network connection service
 */
export class NetworkConnectionService extends BaseService {

	/**
	 * Lists available network connections
	 * @param {NetworkConnectionType | null} type Network connection type
	 * @return {Promise<NetworkConnectionListEntry[]>} List of network connections
	 */
	public async list(type: NetworkConnectionType | null = null): Promise<NetworkConnectionListEntry[]> {
		const config: AxiosRequestConfig = {};
		if (type !== null) {
			Object.assign(config, { params: { type: type } });
		}
		const response: AxiosResponse<NetworkConnectionListEntry[]> =
			await this.axiosInstance.get('/network/connections', config);
		return response.data;
	}

	/**
	 * Creates a new network connection
	 * @param {NetworkConnectionConfiguration} configuration Network connection configuration
	 */
	public async create(configuration: NetworkConnectionConfiguration): Promise<void> {
		await this.axiosInstance.post('/network/connections', configuration);
	}

	/**
	 * Fetches the network connection configuration
	 * @param {string} uuid Network connection UUID
	 * @return {Promise<NetworkConnectionConfiguration>} Network connection configuration
	 */
	public async fetch(uuid: string): Promise<NetworkConnectionConfiguration> {
		const response: AxiosResponse<NetworkConnectionConfiguration> =
			await this.axiosInstance.get(`/network/connections/${uuid}`);
		return response.data;
	}

	/**
	 * Edits the network connection configuration
	 * @param {string} uuid Network connection UUID
	 * @param {NetworkConnectionConfiguration} configuration Network connection configuration
	 */
	public async edit(uuid: string, configuration: NetworkConnectionConfiguration): Promise<void> {
		await this.axiosInstance.put(`/network/connections/${uuid}`, configuration);
	}

	/**
	 * Deletes the network connection configuration
	 * @param {string} uuid Network connection UUID
	 */
	public async delete(uuid: string): Promise<void> {
		await this.axiosInstance.delete(`/network/connections/${uuid}`);
	}

	/**
	 * Connects to the network connection
	 * @param {string} uuid Network connection UUID
	 * @param {string | null} interfaceName Network interface name
	 */
	public async connect(uuid: string, interfaceName: string | null = null): Promise<void> {
		const config: AxiosRequestConfig = {};
		if (interfaceName !== null) {
			Object.assign(config, { params: { 'interface': interfaceName } });
		}
		await this.axiosInstance.post(`/network/connections/${uuid}/connect`, null, config);
	}

	/**
	 * Disconnects from the network connection
	 * @param {string} uuid Network connection UUID
	 */
	public async disconnect(uuid: string): Promise<void> {
		await this.axiosInstance.post(`/network/connections/${uuid}/disconnect`);
	}

}
