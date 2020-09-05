import axios from 'axios';
import {authorizationHeader} from '../helpers/authorizationHeader';

/**
 * IQRF networks service
 */
class IqrfService {
	/**
	 * Retrieves IQRF IDE Macros
	 * @returns {Promise<AxiosResponse<any>>}
	 */
	getMacros() {
		return axios.get('iqrf/macros/', {headers: authorizationHeader()});
	}
}

export default new IqrfService();
