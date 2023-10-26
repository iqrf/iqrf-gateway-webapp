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
import type { AxiosResponse } from 'axios';

import { BaseService } from '../BaseService';
import type { TimeConfig, TimeSet, Timezone } from '../../types/Gateway/Time';

/**
 * Time service
 */
export class TimeService extends BaseService {

	/**
	 * Fetches current gateway time
	 * @return {Promise<TimeConfig>} Current gateway time
	 */
	public getTime(): Promise<TimeConfig> {
		return this.axiosInstance.get('/gateway/time')
			.then((response: AxiosResponse<TimeConfig>): TimeConfig => response.data);
	}

	/**
	 * Fetches available time zones
	 * @return {Promise<Timezone[]>} Available time zones
	 */
	public getTimezones(): Promise<Timezone[]> {
		return this.axiosInstance.get('/gateway/time/timezones')
			.then((response: AxiosResponse<Timezone[]>): Timezone[] => response.data);
	}

	/**
	 * Updates gateway time and ntp configuration
	 * @param {TimeSet} data Time and NTP configuration
	 */
	public setTime(data: TimeSet): Promise<void> {
		return this.axiosInstance.post('/gateway/time', data)
			.then((): void => {return;});
	}
}
