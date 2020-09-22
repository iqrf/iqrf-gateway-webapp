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
		return axios.get('pixla', {headers: authorizationHeader()})
			.then((response: AxiosResponse) => {
				return response.data.token;
			});
	}

	/**
	 * Sets the new device token
	 * @param token Net token to set
	 */
	setToken(token: string): Promise<AxiosResponse> {
		return axios.put('pixla', {token: token}, {headers: authorizationHeader()});
	}
}

export default new PixlaService();
