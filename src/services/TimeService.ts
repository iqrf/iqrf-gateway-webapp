import axios, {AxiosResponse} from 'axios';
import {authorizationHeader} from '../helpers/authorizationHeader';

/**
 * Time management service
 */
class TimeService {

	/**
	 * Retrieves current gateway date, time and timezone
	 */
	getTime(): Promise<AxiosResponse> {
		return axios.get('gateway/time', {headers: authorizationHeader()});
	}

	/**
	 * Retrieves available timezones
	 */
	getTimezones(): Promise<AxiosResponse> {
		return axios.get('gateway/time/timezones', {headers: authorizationHeader()});
	}
}

export default new TimeService();
