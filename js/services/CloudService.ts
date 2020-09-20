import axios, {AxiosResponse} from 'axios';
import {authorizationHeader} from '../helpers/authorizationHeader';

/**
 * Cloud connections service
 */
class CloudService {
	/**
	 * Saves and creates new mqtt cloud connection.
	 * @param serviceName Cloud service name
	 * @param data Cloud connection configuration
	 * @param timeout HTTP timeout
	 */
	create(serviceName: string, data: any, timeout: number): Promise<AxiosResponse> {
		return axios.post('clouds/' + serviceName, data, {headers: authorizationHeader(), timeout: timeout});
	}
}

export default new CloudService();
