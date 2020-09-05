import axios from 'axios';
import {authorizationHeader} from '../helpers/authorizationHeader';

/**
 * Optional feature service
 */
class FeatureService {
	/**
	 * Fetch all features
	 * @returns {Promise<AxiosResponse<any>>}
	 */
	fetchAll() {
		return axios.get('features', {headers: authorizationHeader()});
	}
}

export default new FeatureService();
