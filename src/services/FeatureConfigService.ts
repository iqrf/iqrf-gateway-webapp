import axios, {AxiosResponse} from 'axios';
import {authorizationHeader} from '../helpers/authorizationHeader';

/**
 * Feature configuration service
 */
class FeatureConfigService {	
	/**
	 * Retrieves feature configuration
	 * @param featureName feature name
	 */
	getConfig(featureName: string): Promise<AxiosResponse> {
		return axios.get('config/' + featureName, {headers: authorizationHeader()});
	}

	/**
	 * Saves new feature configuration
	 * @param featureName feature name
	 * @param config new feature configuration
	 */
	saveConfig(featureName: string, config: any): Promise<AxiosResponse> {
		return axios.put('config/' + featureName, config, {headers: authorizationHeader()});
	}
}

export default new FeatureConfigService();
