import axios, {AxiosResponse} from 'axios';
import { authorizationHeader } from '../helpers/authorizationHeader';

/**
 * APT configuration
 */
export interface AptConfiguration {
	'APT::Periodic::Enable': string;
}

/**
 * APT configuration service
 */
class AptService {

	/**
	 * Retrieves APT configuration
	 */
	read(): Promise<AxiosResponse> {
		return axios.get('/config/apt', {headers: authorizationHeader()});
	}

	/**
	 * Sets APT configuration
	 * @param configuration APT configuration
	 */
	write(configuration: AptConfiguration): Promise<AxiosResponse> {
		return axios.put('/config/apt', configuration, {headers: authorizationHeader()});
	}
}

export default new AptService();
