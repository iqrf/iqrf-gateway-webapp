/**
 * Copyright 2023-2024 MICRORISC s.r.o.
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

import { FileResponse } from '../../types';
import { type JournalRecords } from '../../types/Gateway';
import { BaseService } from '../BaseService';

/**
 * Log service
 */
export class LogService extends BaseService {

	/**
	 * Retrieves list of services with available logs
	 * @return {Promise<string[]>} List of services with available logs
	 */
	public async listAvailable(): Promise<string[]> {
		const response: AxiosResponse<string[]> =
			await this.apiClient.getAxiosInstance().get('/gateway/logs');
		return response.data;
	}

	/**
	 * Retrieves service log
	 * @param {string} service Service name
	 * @return {Promise<string>} Service log
	 */
	public async getServiceLog(service: string): Promise<string> {
		const response: AxiosResponse<string> =
			await this.apiClient.getAxiosInstance().get(`/gateway/logs/${service}`);
		return response.data;
	}

	/**
	 * Exports logs archive
	 * @return {Promise<FileResponse<Blob>>} Logs archive
	 */
	public async exportLogs(): Promise<FileResponse<Blob>> {
		const response: AxiosResponse<Blob> =
			await this.apiClient.getAxiosInstance().get('/gateway/logs/export', { responseType: 'blob' });
		return FileResponse.fromAxiosResponse(response);
	}

	/**
	 * Retrieves journal records
	 * @param {number} count Number of records to retrieve
	 * @param {string|null} cursor Cursor of first record
	 * @return {Promise<JournalRecords>} Journal records
	 */
	public async getJournalRecords(count: number, cursor: string | null = null): Promise<JournalRecords> {
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
