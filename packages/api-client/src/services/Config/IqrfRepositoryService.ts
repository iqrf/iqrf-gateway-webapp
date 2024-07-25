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

import { type IqrfRepositoryConfig } from '../../types/Config';
import { BaseService } from '../BaseService';

/**
 * IQRF repository configuration service
 */
export class IqrfRepositoryService extends BaseService {

	/**
	 * Retrieves IQRF repository configuration
	 * @return {Promise<IqrfRepositoryConfig>} IQRF repository configuration
	 */
	public async getConfig(): Promise<IqrfRepositoryConfig> {
		const response: AxiosResponse<IqrfRepositoryConfig> =
			await this.axiosInstance.get('/config/iqrf-repository');
		return response.data;
	}

	/**
	 * Updates IQRF repository configuration
	 * @param {IqrfRepositoryConfig} config IQRF repository configuration
	 */
	public async updateConfig(config: IqrfRepositoryConfig): Promise<void> {
		await this.axiosInstance.put('/config/iqrf-repository', config);
	}

}
