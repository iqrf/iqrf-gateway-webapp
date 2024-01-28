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

import { type MonitCheckWithDefinition, type MonitConfig } from '../../types/Config/Monit';
import { BaseService } from '../BaseService';

/**
 * Monit service
 */
export class MonitService extends BaseService {

	/**
	 * Fetches monit configuration
	 * @return {Promise<MonitConfig>} Monit configuration
	 */
	public getConfig(): Promise<MonitConfig> {
		return this.axiosInstance.get('/config/monit')
			.then((response: AxiosResponse<MonitConfig>): MonitConfig => response.data);
	}

	/**
	 * Edits monit configuration
	 * @param {MonitConfig} config Monit configuration
	 */
	public editConfig(config: MonitConfig): Promise<void> {
		return this.axiosInstance.put('/config/monit', config)
			.then((): void => {return;});
	}

	/**
	 * Fetches monit check
	 * @param {string} name Check name
	 * @return {Promise<MonitCheckWithDefinition>} Monit check
	 */
	public getCheck(name: string): Promise<MonitCheckWithDefinition> {
		return this.axiosInstance.get(`/config/monit/checks/${name}`)
			.then((response: AxiosResponse<MonitCheckWithDefinition>): MonitCheckWithDefinition => response.data);
	}

	/**
	 * Enables monit check
	 * @param {string} name Check name
	 */
	public enableCheck(name: string): Promise<void> {
		return this.axiosInstance.post(`/config/monit/checks/${name}/enable`)
			.then((): void => {return;});
	}

	/**
	 * Disables monit check
	 * @param {string} name Check name
	 */
	public disableCheck(name: string): Promise<void> {
		return this.axiosInstance.post(`/config/monit/checks/${name}/disable`)
			.then((): void => {return;});
	}

}
