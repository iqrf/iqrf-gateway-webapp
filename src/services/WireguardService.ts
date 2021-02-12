import axios, {AxiosResponse} from 'axios';
import {authorizationHeader} from '../helpers/authorizationHeader';

import {Dictionary} from 'vue-router/types/router';
import {IWGTunnel} from '../interfaces/network';

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
	getTunnel(id: number): Promise<AxiosResponse> {
		return axios.get('network/wireguard/' + id, {headers: authorizationHeader()});
	}

	/**
	 * Changes state of Wireguard tunnel 
	 * @param {Dictionary<string|boolean>} config Wireguard tunnel state config
	 */
	changeState(config: Dictionary<string|boolean>): Promise<AxiosResponse> {
		return axios.post('network/wireguard/state', config, {headers: authorizationHeader()});
	}

	/**
	 * Removes an existing Wireguard tunnel
	 * @param {number} id Wireguard tunnel name
	 */
	removeTunnel(id: number): Promise<AxiosResponse> {
		return axios.delete('network/wireguard/' + id, {headers: authorizationHeader()});
	}

}

export default new WireguardService();
