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

/**
 * IQRF Gateway Daemon configuration service
 */
export class IqrfGatewayDaemonService extends BaseService {

	/**
	 * Retrieves IQRF Gateway Daemon component configuration and instances
	 * @param {string} component Component name
	 * @return {Promise<AxiosResponse>} IQRF Gateway Daemon component configuration with instances
	 */
	public getComponent(component: string): Promise<AxiosResponse> {
		return this.axiosInstance.get(`/config/daemon/${encodeURIComponent(component)}`);
	}

	/**
	 * Updates the component instance configuration
	 * @param {string} component Daemon component name
	 * @param {string} instance Daemon component instance name
	 * @param {Record<string, any>} configuration Daemon component instance configuration
	 */
	public updateInstance(component: string, instance: string, configuration: Record<string, any>): Promise<void> {
		return this.axiosInstance.put(`/config/daemon/${encodeURIComponent(component)}/${encodeURIComponent(instance)}`, configuration)
			.then((): void => {return;});
	}
}
