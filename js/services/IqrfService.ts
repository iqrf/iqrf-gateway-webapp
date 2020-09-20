import axios, {AxiosResponse} from 'axios';
import {authorizationHeader} from '../helpers/authorizationHeader';

/**
 * IQRF networks service
 */
class IqrfService {
	/**
	 * Retrieves IQRF IDE Macros
	 */
	getMacros(): Promise<AxiosResponse> {
		return axios.get('iqrf/macros/', {headers: authorizationHeader()});
	}
}

export default new IqrfService();
