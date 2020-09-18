import axios from 'axios';
import {authorizationHeader} from '../helpers/authorizationHeader';

/**
 * Cloud connections service
 */
class CloudService {
	/**
	 * Saves and creates new mqtt cloud connection.
	 * @param serviceName Cloud service name
	 * @param json Cloud connection configuration
	 * @returns {Promise<AxiosResponse<any>>}
	 */
	create(serviceName, json) {
		return axios.post('clouds/' + serviceName, json, {headers: authorizationHeader()});
	}

	/**
	 * Saves and creates new mqtt cloud connection.
	 * @param serviceName Cloud service name
	 * @param json Cloud connection configuration
	 * @param timeout Request timeout
	 * @returns {Promise<AxiosResponse<any>>}
	 */
	createWithTimeout(serviceName, json, timeout) {
		return axios.post('clouds/' + serviceName, json, {headers: authorizationHeader(), timeout: timeout});
	}
}

export default new CloudService();
