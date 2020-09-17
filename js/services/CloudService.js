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
}

export default new CloudService();
