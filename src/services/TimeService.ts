import { auto } from '@popperjs/core';
import axios, {AxiosResponse} from 'axios';
import {authorizationHeader} from '../helpers/authorizationHeader';

/**
 * Time management service
 */
class TimeService {

	/**
	 * Retrieves available timezones
	 */
	getTimezones(): Promise<AxiosResponse> {
		return axios.get('gateway/time/timezones', {headers: authorizationHeader()});
	}
}

export default new TimeService();
