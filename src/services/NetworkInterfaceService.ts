import axios, {AxiosResponse} from 'axios';
import {authorizationHeader} from '../helpers/authorizationHeader';

/**
 * Network interface state enum
 */
export enum InterfaceState {
	CONNECTED = 'connected',
	CONNECTING = 'connecting',
	CONFIGURING = 'connecting (configuring)',
	DEACTIVATING = 'deactivating',
	DISCONNECTED = 'disconnected',
	FAILED = 'connection failed',
	IP_CONFIG = 'connecting (getting IP configuration)',
	IP_CHECK = 'connecting (checking IP connectivity)',
	NEED_AUTH = 'connecting (need authentication)',
	PREPARE = 'connecting (prepare)',
	SECONDARIES = 'connecting (starting secondary connections)',
	UNAVAILABLE = 'unavailable',
	UNMANAGED = 'unmanaged',
	UNKNOWN = 'unknown',
}

/**
 * Network interface type enum
 */
export enum InterfaceType {
	BOND = 'bond',
	BRIDGE = 'bridge',
	DUMMY = 'dummy',
	ETHERNET = 'ethernet',
	LOOPBACK = 'loopback',
	TUN = 'tun',
	VLAN = 'vlan',
	WIFI = 'wifi',
	WIFI_P2P = 'wifi-p2p',
	WIREGUARD = 'wireguard',
}

/**
 * Network interface service
 */
class NetworkInterfaceService {

	/**
	 * Lists available network interfaces
	 * @param type Network interface type
	 */
	public list(type: InterfaceType|null = null): Promise<AxiosResponse> {
		const config = {headers: authorizationHeader()};
		if (type !== null) {
			Object.assign(config, {params: {type: type}});
		}
		return axios.get('network/interfaces', config);
	}

}

export default new NetworkInterfaceService();
