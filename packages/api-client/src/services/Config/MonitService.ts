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

import {
	type MonitConfig,
	type MonitCheck,
} from '../../types/Config';
import { BaseService } from '../BaseService';

/**
 * Monit service
 */
export class MonitService extends BaseService {

	/**
	 * Retrieves Monit configuration
	 * @return {Promise<MonitConfig>} Monit configuration
	 */
	public async getConfig(): Promise<MonitConfig> {
		const response: AxiosResponse<MonitConfig> =
			await this.axiosInstance.get('/config/monit');
		return response.data;
	}

	/**
	 * Updates Monit configuration
	 * @param {MonitConfig} config Monit configuration
	 */
	public async updateConfig(config: MonitConfig): Promise<void> {
		await this.axiosInstance.put('/config/monit', config);
	}

	/**
	 * Retrieves Monit check
	 * @param {string} name Check name
	 * @return {Promise<MonitCheck>} Monit check
	 */
	public async getCheck(name: string): Promise<MonitCheck> {
		const response: AxiosResponse<MonitCheck> =
			await this.axiosInstance.get(`/config/monit/checks/${name}`);
		return response.data;
	}

	/**
	 * Enables monit check
	 * @param {string} name Check name
	 */
	public async enableCheck(name: string): Promise<void> {
		await this.axiosInstance.post(`/config/monit/checks/${name}/enable`);
	}

	/**
	 * Disables monit check
	 * @param {string} name Check name
	 */
	public async disableCheck(name: string): Promise<void> {
		await this.axiosInstance.post(`/config/monit/checks/${name}/disable`);
	}

}
