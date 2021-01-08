import axios, {AxiosResponse} from 'axios';
import {authorizationHeader} from '../helpers/authorizationHeader';

/**
 * Time management service
 */
class TimeService {

	/**
	 * Retrieves current gateway date, time and timezone
	 * @returns {Promise<AxiosResponse>} REST API response promise
	 */
	getTime(): Promise<AxiosResponse> {
		return axios.get('gateway/time', {headers: authorizationHeader()});
	}

	/**
	 * Retrieves available timezones
	 * @returns {Promise<AxiosResponse>} REST API response promise
	 */
	getTimezones(): Promise<AxiosResponse> {
		return axios.get('gateway/time/timezones', {headers: authorizationHeader()});
	}

	/**
	 * Sets new timezone
	 * @param timezone Timezone name
	 * @returns {Promise<AxiosResponse>} REST API response promise
	 */
	setTimezone(timezone: string): Promise<AxiosResponse> {
		return axios.put('gateway/time/timezone/' + encodeURIComponent(timezone), null, {headers: authorizationHeader()});
	}
}

export default new TimeService();
