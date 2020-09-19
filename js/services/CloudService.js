import axios from 'axios';
import {authorizationHeader} from '../helpers/authorizationHeader';

/**
 * Cloud connections service
 */
class CloudService {
	/**
	 * Saves and creates new mqtt cloud connection.
	 * @param serviceName Cloud service name
	 * @param data Cloud connection configuration
	 * @returns {Promise<AxiosResponse<any>>}
	 */
	create(serviceName, data, timeout) {
		return axios.post('clouds/' + serviceName, data, {headers: authorizationHeader(), timeout: timeout});
	}
}

export default new CloudService();
