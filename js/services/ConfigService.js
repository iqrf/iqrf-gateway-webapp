import axios from 'axios';
import {authorizationHeader} from '../helpers/authorizationHeader';

/**
 * Service configuration service
 */
class ConfigService {
	/**
	 * Retrieves service configuration
	 * @returns {Promise<AxiosResponse<<any>>}
	 */
	getConfig(serviceName) {
		return axios.get(serviceName, {headers: authorizationHeader()});
	}

	/**
	 * Saves new service configuration
	 * @returns {Promise<AxiosResponse<<any>>}
	 */
	saveConfig(serviceName, config) {
		return axios.put(serviceName, config, {headers: authorizationHeader()});
	}
}

export default new ConfigService();
