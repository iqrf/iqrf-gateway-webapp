import axios from 'axios';
import {authorizationHeader} from '../helpers/authorizationHeader';

/**
 * PIXLA management service
 */
class PixlaService {
	/**
	 * Retrieves the device token
	 * @returns {Promise<AxiosResponse<any>>}
	 */
	getToken() {
		return axios.get('pixla', {headers: authorizationHeader()});
	}
}

export default new PixlaService();
