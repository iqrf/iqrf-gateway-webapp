import axios from 'axios';
import {authorizationHeader} from '../helpers/authorizationHeader';

/**
 * Service configuration service
 */
class ConfigService {
	/**
	 * Retrieves service configuration
	 * @returns {Promise<AxiosResponse<any>>}
	 */
	getConfig(serviceName, timeout) {
		return axios.get(serviceName, {headers: authorizationHeader(), timeout: timeout});
	}

	/**
	 * Saves new service configuration
	 * @returns {Promise<AxiosResponse<any>>}
	 */
	saveConfig(serviceName, config, timeout) {
		return axios.put(serviceName, config, {headers: authorizationHeader(), timeout: timeout});
	}
}

export default new ConfigService();
