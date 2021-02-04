import axios, {AxiosResponse} from 'axios';
import {authorizationHeader} from '../helpers/authorizationHeader';

/**
 * Wireguard VPN service
 */
class WireguardService {

	/**
	 * Creates a new Wireguard interface
	 * @param {string} name Wireguard interface name
	 */
	public createInterface(name: string): Promise<AxiosResponse> {
		return axios.post('network/wireguard/interface/' + name, null, {headers: authorizationHeader()});
	}

	/**
	 * Removes an existing Wireguard interface
	 * @param {string} name Wireguard interface name
	 */
	public removeInterface(name: string): Promise<AxiosResponse> {
		return axios.delete('network/wireguard/interface/' + name, {headers: authorizationHeader()});
	}

	/**
	 * Retrieves list of existing key pairs
	 */
	public listKeys(): Promise<AxiosResponse> {
		return axios.get('network/wireguard/keys', {headers: authorizationHeader()});
	}

	/**
	 * Creates a new Wireguard key-pair
	 * @param {string} name Wireguard key-pair name
	 */
	public createKeys(name: string): Promise<AxiosResponse> {
		return axios.post('network/wireguard/keys/' + name, null, {headers: authorizationHeader()});
	}
}

export default new WireguardService();
