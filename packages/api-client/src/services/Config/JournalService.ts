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
	type JournalRecords,
	type JournalConfig,
} from '../../types/Config/Journal';
import { BaseService } from '../BaseService';

/**
 * Journal service
 */
export class JournalService extends BaseService {

	/**
	 * Fetches journal configuration
	 * @return {Promise<JournalConfig>} Journal configuration
	 */
	public getConfig(): Promise<JournalConfig> {
		return this.axiosInstance.get('/gateway/journal/config')
			.then((response: AxiosResponse<JournalConfig>): JournalConfig => response.data);
	}

	/**
	 * Edits journal configuration
	 * @param {JournalConfig} config Journal configuration
	 */
	public editConfig(config: JournalConfig): Promise<void> {
		return this.axiosInstance.post('/gateway/journal/config', config)
			.then((): void => {return;});
	}

	/**
	 * Fetches journal records
	 * @param {number} count Number of records to retrieve
	 * @param {string|null} cursor Cursor of first record
	 * @return {Promise<JournalRecords>} Journal records
	 */
	public getRecords(count: number, cursor: string|null = null): Promise<JournalRecords> {
		const params: Record<string, number|string> = {
			count: count,
		};
		if (cursor) {
			params.cursor = cursor;
		}
		return this.axiosInstance.get('/gateway/journal', { params: params })
			.then((response: AxiosResponse<JournalRecords>): JournalRecords => response.data);
	}
}
