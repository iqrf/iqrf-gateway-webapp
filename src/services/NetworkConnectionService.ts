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
import {authorizationHeader} from '../helpers/authorizationHeader';

export enum ConnectionType {
	BLUETOOTH = 'bluetooth',
	BRIDGE = 'bridge',
	DUMMY = 'dummy',
	ETHERNET = '802-3-ethernet',
	GSM = 'gsm',
	INFINIBAND = 'infiniband',
	TUN = 'tun',
	VLAN = 'vlan',
	VPN = 'vpn',
	WIFI = '802-11-wireless',
	WIMAX = 'wimax',
	WIREGUARD = 'wireguard',
	WPAN = 'wpan',
}

/**
 * Network connection service
 */
class NetworkConnectionService {

	/**
	 * Connects the network connection
	 * @param uuid Network connection UUID
	 * @param interfaceName Network interface name
	 */
	public connect(uuid: string, interfaceName: string|null = null): Promise<AxiosResponse> {
		const config = {headers: authorizationHeader()};
		if (interfaceName !== null) {
			Object.assign(config, {params: {'interface': interfaceName}});
		}
		return axios.post('network/connections/' + uuid + '/connect', null, config);
	}

	/**
	 * Disconnects the network connection
	 * @param uuid Network connection UUID
	 */
	public disconnect(uuid: string): Promise<AxiosResponse> {
		const config = {headers: authorizationHeader()};
		return axios.post('network/connections/' + uuid + '/disconnect', null, config);
	}

	/**
	 * Adds a new network connection
	 * @param configuration Network connection configuration
	 */
	public add(configuration: any): Promise<AxiosResponse> {
		return axios.post('network/connections/', configuration, {headers: authorizationHeader()});
	}

	/**
	 * Edits the network configuration
	 * @param uuid Network configuration UUID
	 * @param configuration Network connection cionfiguration
	 */
	public edit(uuid: string, configuration: any): Promise<AxiosResponse> {
		const config = {headers: authorizationHeader()};
		return axios.put('network/connections/' + uuid, configuration, config);
	}

	/**
	 * Retrieves the network connection configuration
	 * @param uuid Network connection UUID
	 */
	public get(uuid: string): Promise<AxiosResponse> {
		return axios.get('network/connections/' + uuid, {headers: authorizationHeader()});
	}

	/**
	 * Removes an existing network connection configuration
	 * @param uuid Network connection UUID
	 */
	public remove(uuid: string): Promise<AxiosResponse> {
		return axios.delete('network/connections/' + uuid, {headers: authorizationHeader()});
	}

	/**
	 * Lists available network connections
	 * @param type Network connection type
	 */
	public list(type: ConnectionType|null = null): Promise<AxiosResponse> {
		const config = {headers: authorizationHeader()};
		if (type !== null) {
			Object.assign(config, {params: {type: type}});
		}
		return axios.get('network/connections', config);
	}

	/**
	 * Lists available wifi access points
	 */
	public listWifiAccessPoints(): Promise<AxiosResponse> {
		return axios.get('network/wifi/list', {headers: authorizationHeader()});
	}

}

export default new NetworkConnectionService();
