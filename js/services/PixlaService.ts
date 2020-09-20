import axios, {AxiosResponse} from 'axios';
import {authorizationHeader} from '../helpers/authorizationHeader';

/**
 * PIXLA management service
 */
class PixlaService {
	/**
	 * Retrieves the device token
	 */
	getToken(): Promise<AxiosResponse> {
		return axios.get('pixla', {headers: authorizationHeader()});
	}
}

export default new PixlaService();
