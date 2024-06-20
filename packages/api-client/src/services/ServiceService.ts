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

import { type ServiceState, type ServiceStatus } from '../types';

import { BaseService } from './BaseService';

/**
 * System service service
 */
export class ServiceService extends BaseService {

	/**
	 * Retrieves the state of supported services
	 * @param {boolean} withStatus Include service status
	 * @returns {Promise<ServiceState[]>} State of supported services
	 */
	public async list(withStatus: boolean = false): Promise<ServiceState[]> {
		const response: AxiosResponse<ServiceState[]> =
			await this.axiosInstance.get('/services', {
				params: {
					withStatus: withStatus,
				},
			});
		return response.data;
	}

	/**
	 * Retrieves the service status
	 * @param {string} name Service name
	 * @returns {Promise<ServiceStatus>} Service status
	 */
	public async getStatus(name: string): Promise<ServiceStatus> {
		const response: AxiosResponse<ServiceStatus> =
			await this.axiosInstance.get(`/services/${name}`);
		return response.data;
	}

	/**
	 * Enables the service
	 * @param {string} name Service name
	 */
	public async enable(name: string): Promise<void> {
		await this.axiosInstance.post(`/services/${name}/enable`);
	}

	/**
	 * Disables the service
	 * @param {string} name Service name
	 */
	public async disable(name: string): Promise<void> {
		await this.axiosInstance.post(`/services/${name}/disable`);
	}

	/**
	 * Starts the service
	 * @param {string} name Service name
	 */
	public async start(name: string): Promise<void> {
		await this.axiosInstance.post(`/services/${name}/start`);
	}

	/**
	 * Stops the service
	 * @param {string} name Service name
	 */
	public async stop(name: string): Promise<void> {
		await this.axiosInstance.post(`/services/${name}/stop`);
	}

	/**
	 * Restarts the service
	 * @param {string} name Service name
	 */
	public async restart(name: string): Promise<void> {
		await this.axiosInstance.post(`/services/${name}/restart`);
	}

}
