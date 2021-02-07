import axios, {AxiosResponse} from 'axios';
import {authorizationHeader} from '../helpers/authorizationHeader';
import {IWGTunnel} from '../interfaces/network';

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
	 */
	public createKeys(): Promise<AxiosResponse> {
		return axios.post('network/wireguard/keypair', null, {headers: authorizationHeader()});
	}

	/**
	 * Creates a new Wireguard VPN tunnel
	 * @param {IWGTunnel} data Wireguard tunnel configuration
	 */
	public createTunnel(data: IWGTunnel): Promise<AxiosResponse> {
		return axios.post('network/wireguard', data, {headers: authorizationHeader()});
	}
}

export default new WireguardService();
