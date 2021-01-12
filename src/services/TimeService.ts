import axios, {AxiosResponse} from 'axios';
import { Dictionary } from 'vue-router/types/router';
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
	 * Sets new time
	 * @param {Dictionary<boolean|number} data Timestamp
	 * @returns {Promise<AxiosResponse>} REST API response promise
	 */
	setTime(data: Dictionary<boolean|number>): Promise<AxiosResponse> {
		return axios.put('gateway/time', data, {headers: authorizationHeader()});
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
	 * @param {Dictionary<string>} data Timezone name
	 * @returns {Promise<AxiosResponse>} REST API response promise
	 */
	setTimezone(data: Dictionary<string>): Promise<AxiosResponse> {
		return axios.put('gateway/time/timezone/', data, {headers: authorizationHeader()});
	}
}

export default new TimeService();
