import axios, {AxiosResponse} from 'axios';
import {authorizationHeader} from '../helpers/authorizationHeader';
import {IMenderConfig, IMonitConfig} from '../interfaces/maintenance';

/**
 * Maintenance service
 */
class MaintenanceService {

	/**
	 * Retrieves Mender configuration
	 * @returns 
	 */
	getMenderConfig(): Promise<AxiosResponse> {
		return axios.get('mender', {headers: authorizationHeader()});
	}

	/**
	 * Saves Mender configuration
	 * @param {IMenderConfig} config Mender configuration
	 */
	saveMenderConfig(config: IMenderConfig): Promise<AxiosResponse> {
		return axios.put('mender', config, {headers: authorizationHeader()});
	}

	/**
	 * Retrieves MMonit configuration
	 */
	getMonitConfig(): Promise<AxiosResponse> {
		return axios.get('monit', {headers: authorizationHeader()});
	}

	/**
	 * Saves MMonit configuration
	 * @param {IMonitConfig} config MMonit configuration
	 */
	saveMonitConfig(config: IMonitConfig): Promise<AxiosResponse> {
		return axios.put('monit', config, {headers: authorizationHeader()});
	}
}

export default new MaintenanceService();
