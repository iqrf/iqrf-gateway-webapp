/**
 * Copyright 2023 MICRORISC s.r.o.
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

import { type AxiosResponse } from 'axios';

import {
	type TimeConfig,
	type TimeSet,
	type Timezone,
} from '../../types/Gateway/Time';
import { BaseService } from '../BaseService';

/**
 * Time service
 */
export class TimeService extends BaseService {

	/**
	 * Fetches current gateway time
	 * @return {Promise<TimeConfig>} Current gateway time
	 */
	public async getTime(): Promise<TimeConfig> {
		const response: AxiosResponse<TimeConfig> =
			await this.axiosInstance.get('/gateway/time');
		return response.data;
	}

	/**
	 * Fetches available time zones
	 * @return {Promise<Timezone[]>} Available time zones
	 */
	public async getTimezones(): Promise<Timezone[]> {
		const response: AxiosResponse<Timezone[]> =
			await this.axiosInstance.get('/gateway/time/timezones');
		return response.data;
	}

	/**
	 * Updates gateway time and ntp configuration
	 * @param {TimeSet} data Time and NTP configuration
	 */
	public async setTime(data: TimeSet): Promise<void> {
		await this.axiosInstance.post('/gateway/time', data);
	}
}
