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
	public list(type: NetworkConnectionType|null = null): Promise<NetworkConnectionListEntry[]> {
		const config: AxiosRequestConfig = {};
		if (type !== null) {
			Object.assign(config, { params: { type: type } });
		}
		return this.axiosInstance.get('/network/connections', config)
			.then((response: AxiosResponse<NetworkConnectionListEntry[]>): NetworkConnectionListEntry[] => response.data);
	}

	/**
	 * Creates a new network connection
	 * @param {NetworkConnectionConfiguration} configuration Network connection configuration
	 */
	public create(configuration: NetworkConnectionConfiguration): Promise<void> {
		return this.axiosInstance.post('/network/connections', configuration)
			.then((): void => {return;});
	}

	/**
	 * Fetches the network connection configuration
	 * @param {string} uuid Network connection UUID
	 * @return {Promise<NetworkConnectionConfiguration>} Network connection configuration
	 */
	public fetch(uuid: string): Promise<NetworkConnectionConfiguration> {
		return this.axiosInstance.get(`/network/connections/${uuid}`)
			.then((response: AxiosResponse<NetworkConnectionConfiguration>): NetworkConnectionConfiguration => response.data);
	}

	/**
	 * Edits the network connection configuration
	 * @param {string} uuid Network connection UUID
	 * @param {NetworkConnectionConfiguration} configuration Network connection configuration
	 */
	public edit(uuid: string, configuration: NetworkConnectionConfiguration): Promise<void> {
		return this.axiosInstance.put(`/network/connections/${uuid}`, configuration)
			.then((): void => {return;});
	}

	/**
	 * Deletes the network connection configuration
	 * @param {string} uuid Network connection UUID
	 */
	public delete(uuid: string): Promise<void> {
		return this.axiosInstance.delete(`/network/connections/${uuid}`)
			.then((): void => {return;});
	}

	/**
	 * Connects to the network connection
	 * @param {string} uuid Network connection UUID
	 * @param {string | null} interfaceName Network interface name
	 */
	public connect(uuid: string, interfaceName: string|null = null): Promise<void> {
		const config: AxiosRequestConfig = {};
		if (interfaceName !== null) {
			Object.assign(config, { params: { 'interface': interfaceName } });
		}
		return this.axiosInstance.post(`/network/connections/${uuid}/connect`, null, config)
			.then((): void => {return;});
	}

	/**
	 * Disconnects from the network connection
	 * @param {string} uuid Network connection UUID
	 */
	public disconnect(uuid: string): Promise<void> {
		return this.axiosInstance.post(`/network/connections/${uuid}/disconnect`)
			.then((): void => {return;});
	}

}
