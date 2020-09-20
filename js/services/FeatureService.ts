import axios, {AxiosResponse} from 'axios';
import {authorizationHeader} from '../helpers/authorizationHeader';

/**
 * Optional feature service
 */
class FeatureService {
	/**
	 * Fetch all features
	 */
	fetchAll(): Promise<AxiosResponse> {
		return axios.get('features', {headers: authorizationHeader()});
	}
}

export default new FeatureService();
