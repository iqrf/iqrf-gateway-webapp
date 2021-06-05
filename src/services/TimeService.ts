/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
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
