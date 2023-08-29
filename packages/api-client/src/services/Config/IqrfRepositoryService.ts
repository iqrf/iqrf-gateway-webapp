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

import type {AxiosResponse} from 'axios';

import {BaseService} from '../BaseService';
import type {IqrfRepositoryConfig} from '../../types/Config';

/**
 * IQRF repository configuration service
 */
export class IqrfRepositoryService extends BaseService {

	/**
	 * Fetches IQRF repository configuration
	 * @return {Promise<IqrfRepositoryConfig>} IQRF repository configuration
	 */
	public fetch(): Promise<IqrfRepositoryConfig> {
		return this.axiosInstance.get('/config/iqrf-repository')
			.then((response: AxiosResponse<IqrfRepositoryConfig>): IqrfRepositoryConfig => response.data);
	}

	/**
	 * Sets IQRF repository configuration
	 * @param {IqrfRepositoryConfig} config IQRF repository configuration
	 */
	public edit(config: IqrfRepositoryConfig): Promise<void> {
		return this.axiosInstance.put('/config/iqrf-repository', config)
			.then((): void => {return;});
	}

}