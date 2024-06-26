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

import { BaseService } from '../BaseService';

/**
 * Log service
 */
export class LogService extends BaseService {

	/**
	 * Fetches list of services with available logs
	 * @return {Promise<string[]>} List of services with available logs
	 */
	public async listAvailable(): Promise<string[]> {
		const response: AxiosResponse<string[]> =
			await this.apiClient.getAxiosInstance().get('/gateway/logs');
		return response.data;
	}

	/**
	 * Fetches service log
	 * @param {string} service Service name
	 * @returns {Promise<string>} Service log
	 */
	public async getServiceLog(service: string): Promise<string> {
		const response: AxiosResponse<string> =
			await this.apiClient.getAxiosInstance().get(`/gateway/logs/${service}`);
		return response.data;
	}

	/**
	 * Exports logs archive
	 * @returns {Promise<ArrayBuffer>} Logs archive
	 */
	public async exportLogs(): Promise<ArrayBuffer> {
		const response: AxiosResponse<ArrayBuffer> =
			await this.apiClient.getAxiosInstance().get('/gateway/logs/export', { responseType: 'arraybuffer' });
		return response.data;
	}

}
