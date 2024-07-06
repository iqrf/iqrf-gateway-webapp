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
	type JournalConfig,
	type JournalRecords,
} from '../../types/Config';
import { BaseService } from '../BaseService';

/**
 * Journal service
 */
export class JournalService extends BaseService {

	/**
	 * Fetches journal configuration
	 * @return {Promise<JournalConfig>} Journal configuration
	 */
	public async getConfig(): Promise<JournalConfig> {
		const response: AxiosResponse<JournalConfig> =
			await this.axiosInstance.get('/gateway/journal/config');
		return response.data;
	}

	/**
	 * Edits journal configuration
	 * @param {JournalConfig} config Journal configuration
	 */
	public async editConfig(config: JournalConfig): Promise<void> {
		await this.axiosInstance.put('/gateway/journal/config', config);
	}

	/**
	 * Fetches journal records
	 * @param {number} count Number of records to retrieve
	 * @param {string|null} cursor Cursor of first record
	 * @return {Promise<JournalRecords>} Journal records
	 */
	public async getRecords(count: number, cursor: string | null = null): Promise<JournalRecords> {
		const params: Record<string, number | string> = {
			count: count,
		};
		if (cursor) {
			params.cursor = cursor;
		}
		const response: AxiosResponse<JournalRecords> =
			await this.axiosInstance.get('/gateway/journal', { params: params });
		return response.data;
	}
}
