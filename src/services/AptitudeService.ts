import axios, {AxiosResponse} from 'axios';
import { authorizationHeader } from '../helpers/authorizationHeader';

class AptitudeService {
	/**
	 * Retrieves status of unattended upgrades service
	 */
	getStatus(): Promise<AxiosResponse> {
		return axios.get('/config/apt', {headers: authorizationHeader()});
	}

	/**
	 * Sets status of unattended upgrades service
	 * @param enabled Should service be enabled?
	 */
	setUnattendedUpgrades(enabled: boolean): Promise<AxiosResponse> {
		return axios.post('/config/apt', {'enabled': enabled}, {headers: authorizationHeader()});
	}
}

export default new AptitudeService();