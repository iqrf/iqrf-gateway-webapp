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

import {authorizationHeader} from '@/helpers/authorizationHeader';

import axios, {AxiosResponse} from 'axios';
import {IJournal} from '@/interfaces/Gateway/Journal';

/**
 * Journal service
 */
class JournalService {
	/**
	 * Retrieves systemd journal configuration
	 */
	getConfig(): Promise<AxiosResponse> {
		return axios.get('gateway/journal/config', {headers: authorizationHeader()});
	}

	/**
	 * Saves systemd journal configuration
	 * @param {IJournal} config New configuration
	 */
	saveConfig(config: IJournal): Promise<AxiosResponse> {
		return axios.post('gateway/journal/config', config, {headers: authorizationHeader()});
	}

	/**
	 * Retrieves journal records
	 * @param {number} count Number of records to fetch
	 * @param {string|null} cursor Cursor to start from
	 */
	getRecords(count: number, cursor: string|null = null): Promise<AxiosResponse> {
		const config = {
			headers: authorizationHeader(),
			params: {
				count: count,
			},
		};
		if (cursor !== null) {
			config.params['cursor'] = cursor;
		}
		return axios.get('gateway/journal', config);
	}
}

export default new JournalService();
 