import axios, {AxiosResponse} from 'axios';
import {authorizationHeader} from '../helpers/authorizationHeader';

/**
 * Service configuration service
 */
class ConfigService {
	/**
	 * Retrieves service configuration
	 */
	getConfig(serviceName: string, timeout: number): Promise<AxiosResponse> {
		return axios.get('config/' + serviceName, {headers: authorizationHeader(), timeout: timeout});
	}

	/**
	 * Saves new service configuration
	 */
	saveConfig(serviceName: string, config: any, timeout: number): Promise<AxiosResponse> {
		return axios.put('config/' + serviceName, config, {headers: authorizationHeader(), timeout: timeout});
	}

	/**
	 * Exports daemon configuration
	 */
	exportConfig(timeout: number): Promise<AxiosResponse> {
		return axios.get('config/daemon/migration/export', {headers: authorizationHeader(), timeout: timeout, responseType: 'arraybuffer'});
	}

	/**
	 * Imports daemon configuration
	 */
	importConfig(config: any, timeout: number): Promise<AxiosResponse> {
		return axios.post('config/daemon/migration/import', config, {headers: authorizationHeader(), timeout: timeout});
	}
}

export default new ConfigService();
