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
