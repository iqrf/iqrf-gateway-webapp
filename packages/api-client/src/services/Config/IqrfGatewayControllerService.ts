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
	type IqrfGatewayControllerConfig,
	type IqrfGatewayControllerMapping,
} from '../../types/Config';
import { BaseService } from '../BaseService';

/**
 * IQRF Gateway Controller configuration service
 */
export class IqrfGatewayControllerService extends BaseService {

	/**
	 * Fetch IQRF Gateway Controller configuration
	 * @return {Promise<IqrfGatewayControllerConfig>} IQRF Gateway Controller configuration
	 */
	public async fetchConfig(): Promise<IqrfGatewayControllerConfig> {
		const response: AxiosResponse<IqrfGatewayControllerConfig> = await this.axiosInstance.get('/config/controller');
		return response.data;
	}

	/**
	 * Saves IQRF Gateway Controller configuration
	 * @param {IqrfGatewayControllerConfig} config IQRF Gateway Controller configuration
	 */
	public async saveConfig(config: IqrfGatewayControllerConfig): Promise<void> {
		await this.axiosInstance.put('/config/controller', config);
	}

	/**
	 * List IQRF Gateway Controller mappings
	 * @return {Promise<IqrfGatewayControllerMapping[]>} IQRF Gateway Controller mappings
	 */
	public async listMappings(): Promise<IqrfGatewayControllerMapping[]> {
		const response: AxiosResponse<IqrfGatewayControllerMapping[]> = await this.axiosInstance.get('/config/controller/pins');
		return response.data;
	}

	/**
	 * Fetch IQRF Gateway Controller mapping
	 * @param {number} id IQRF Gateway Controller mapping ID
	 * @return {Promise<IqrfGatewayControllerMapping>} IQRF Gateway Controller mapping
	 */
	public async fetchMapping(id: number): Promise<IqrfGatewayControllerMapping> {
		const response: AxiosResponse<IqrfGatewayControllerMapping> = await this.axiosInstance.get(`/config/controller/pins/${id.toString()}`);
		return response.data;
	}

	/**
	 * Create IQRF Gateway Controller mapping
	 * @param {IqrfGatewayControllerMapping} mapping IQRF Gateway Controller mapping to create
	 */
	public async createMapping(mapping: IqrfGatewayControllerMapping): Promise<void> {
		delete mapping.id;
		await this.axiosInstance.post('/config/controller/pins', mapping);
	}

	/**
	 * Delete IQRF Gateway Controller mapping
	 * @param {number} id IQRF Gateway Controller mapping ID
	 */
	public async deleteMapping(id: number): Promise<void> {
		await this.axiosInstance.delete(`/config/controller/pins/${id.toString()}`);
	}

	/**
	 * Edit IQRF Gateway Controller mapping
	 * @param {number} id IQRF Gateway Controller mapping ID
	 * @param {IqrfGatewayControllerMapping} mapping IQRF Gateway Controller mapping to edit
	 */
	public async editMapping(id: number, mapping: IqrfGatewayControllerMapping): Promise<void> {
		await this.axiosInstance.put(`/config/controller/pins/${id.toString()}`, mapping);
	}

}
