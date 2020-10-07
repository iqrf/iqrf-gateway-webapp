import axios, {AxiosResponse} from 'axios';
import {authorizationHeader} from '../helpers/authorizationHeader';

/**
 * Feature
 */
export interface Feature {

	/**
	 * Feature enablement
	 */
	enabled: boolean;

	/**
	 * Feature URL
	 */
	url?: string;

}

/**
 * Features
 */
export type Features = Record<string, Feature>;

/**
 * Optional feature service
 */
class FeatureService {

	/**
	 * Fetch all features
	 */
	fetchAll(): Promise<Features> {
		return axios.get('features', {headers: authorizationHeader()})
			.then((response: AxiosResponse) => {
				return response.data as Features;
			});
	}

}

export default new FeatureService();
