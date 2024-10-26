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
	IPv4ConfigurationMethod,
	IPv6ConfigurationMethod,
	type NetworkConnectionConfiguration,
	type NetworkConnectionCreated,
	type NetworkConnectionListEntry,
	type NetworkConnectionType,
	WifiSecurityType,
} from '../../types/Network';
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
	 * @return {Promise<NetworkConnectionCreated>} Created network connection
	 */
	public async create(configuration: NetworkConnectionConfiguration): Promise<NetworkConnectionCreated> {
		const response: AxiosResponse<NetworkConnectionCreated> =
			await this.axiosInstance.post('/network/connections', this.prepareConfigurationForSave(configuration));
		return response.data;
	}

	/**
	 * Retrieves the network connection configuration
	 * @param {string} uuid Network connection UUID
	 * @return {Promise<NetworkConnectionConfiguration>} Network connection configuration
	 */
	public async get(uuid: string): Promise<NetworkConnectionConfiguration> {
		const response: AxiosResponse<NetworkConnectionConfiguration> =
			await this.axiosInstance.get(`/network/connections/${uuid}`);
		return response.data;
	}

	/**
	 * Updates the network connection configuration
	 * @param {string} uuid Network connection UUID
	 * @param {NetworkConnectionConfiguration} configuration Network connection configuration
	 */
	public async update(uuid: string, configuration: NetworkConnectionConfiguration): Promise<void> {
		await this.axiosInstance.put(`/network/connections/${uuid}`, this.prepareConfigurationForSave(configuration));
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

	/**
	 * Prepares the network connection configuration for saving (adding or updating)
	 * @param {NetworkConnectionConfiguration} configuration Network connection configuration
	 * @return {NetworkConnectionConfiguration} Prepared network connection configuration
	 */
	private prepareConfigurationForSave(configuration: NetworkConnectionConfiguration): NetworkConnectionConfiguration {
		const preparedConfiguration: NetworkConnectionConfiguration = { ...configuration };
		// Remove unnecessary serial configuration fields if not serial connection
		if (
			!/tty(?:AMA|AMC|S)\d+/.test(preparedConfiguration.interface ?? '') &&
			preparedConfiguration.serial !== undefined
		) {
			delete preparedConfiguration.serial;
		}
		// Remove unnecessary IPv4 configuration fields
		if (
			[
				IPv4ConfigurationMethod.AUTO,
				IPv4ConfigurationMethod.DISABLED,
			].includes(preparedConfiguration.ipv4.method)
		) {
			preparedConfiguration.ipv4.addresses = preparedConfiguration.ipv4.dns = [];
			preparedConfiguration.ipv4.gateway = null;
		}
		// Remove unnecessary IPv6 configuration fields
		if (
			[
				IPv6ConfigurationMethod.AUTO,
				IPv6ConfigurationMethod.DISABLED,
				IPv6ConfigurationMethod.DHCP,
			].includes(preparedConfiguration.ipv6.method)
		) {
			preparedConfiguration.ipv6.addresses = preparedConfiguration.ipv6.dns = [];
			preparedConfiguration.ipv6.gateway = null;
		}
		// Remove unnecessary Wi-Fi fields
		if (preparedConfiguration.wifi !== undefined) {
			delete preparedConfiguration.wifi.bssids;
			switch (preparedConfiguration.wifi.security.type) {
				case WifiSecurityType.Open:
					delete preparedConfiguration.wifi.security.eap;
					delete preparedConfiguration.wifi.security.leap;
					delete preparedConfiguration.wifi.security.psk;
					delete preparedConfiguration.wifi.security.wep;
					break;
				case WifiSecurityType.LEAP:
					delete preparedConfiguration.wifi.security.eap;
					delete preparedConfiguration.wifi.security.psk;
					delete preparedConfiguration.wifi.security.wep;
					break;
				case WifiSecurityType.WEP:
					delete preparedConfiguration.wifi.security.eap;
					delete preparedConfiguration.wifi.security.leap;
					delete preparedConfiguration.wifi.security.psk;
					break;
				case WifiSecurityType.WPA_EAP:
					delete preparedConfiguration.wifi.security.leap;
					delete preparedConfiguration.wifi.security.psk;
					delete preparedConfiguration.wifi.security.wep;
					break;
				case WifiSecurityType.WPA_PSK:
					delete preparedConfiguration.wifi.security.eap;
					delete preparedConfiguration.wifi.security.leap;
					delete preparedConfiguration.wifi.security.wep;
					break;
			}
		}
		return preparedConfiguration;
	}

}
