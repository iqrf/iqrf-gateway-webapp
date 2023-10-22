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

import type {AxiosResponse} from 'axios';

import {BaseService} from './BaseService';
import type {ServiceState, ServiceStatus} from '../types';

/**
 * System service service
 */
export class ServiceService extends BaseService {

	/**
	 * Retrieves the state of supported services
	 * @param {boolean} withStatus Include service status
	 * @returns {Promise<ServiceState[]>} State of supported services
	 */
	public list(withStatus = false): Promise<ServiceState[]> {
		return this.axiosInstance.get('/services', {
			params: {
				withStatus: withStatus,
			},
		})
			.then((response: AxiosResponse<ServiceState[]>): ServiceState[] => response.data);
	}

	/**
	 * Retrieves the service status
	 * @param {string} name Service name
	 * @returns {Promise<ServiceStatus>} Service status
	 */
	public getStatus(name: string): Promise<ServiceStatus> {
		return this.axiosInstance.get('/services/' + name)
			.then((response: AxiosResponse<ServiceStatus>): ServiceStatus => response.data);
	}

	/**
	 * Enables the service
	 * @param {string} name Service name
	 */
	public enable(name: string): Promise<void> {
		return this.axiosInstance.post('/services/' + name + '/enable')
			.then((): void => {return;});
	}

	/**
	 * Disables the service
	 * @param {string} name Service name
	 */
	public disable(name: string): Promise<void> {
		return this.axiosInstance.post('/services/' + name + '/disable')
			.then((): void => {return;});
	}

	/**
	 * Starts the service
	 * @param {string} name Service name
	 */
	public start(name: string): Promise<void> {
		return this.axiosInstance.post('/services/' + name + '/start')
			.then((): void => {return;});
	}

	/**
	 * Stops the service
	 * @param {string} name Service name
	 */
	public stop(name: string): Promise<void> {
		return this.axiosInstance.post('/services/' + name + '/stop')
			.then((): void => {return;});
	}

	/**
	 * Restarts the service
	 * @param {string} name Service name
	 */
	public restart(name: string): Promise<void> {
		return this.axiosInstance.post('/services/' + name + '/restart')
			.then((): void => {return;});
	}

}
