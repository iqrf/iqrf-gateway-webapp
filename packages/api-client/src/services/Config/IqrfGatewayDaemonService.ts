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
	FileResponse,
} from '../../types';
import {
	type IqrfGatewayDaemonComponent,
	type IqrfGatewayDaemonComponentInstanceConfiguration,
	type IqrfGatewayDaemonComponentName,
	type IqrfGatewayDaemonComponentState,
	type IqrfGatewayDaemonConfig,
	type IqrfGatewayDaemonMapping,
	type IqrfGatewayDaemonSchedulerMessagings,
	type MappingType,
} from '../../types/Config';
import { BaseService } from '../BaseService';

/**
 * IQRF Gateway Daemon configuration service
 */
export class IqrfGatewayDaemonService extends BaseService {

	/**
	 * Retrieves IQRF Gateway Daemon main configuration
	 * @return {Promise<IqrfGatewayDaemonConfig>} IQRF Gateway Daemon main configuration
	 */
	public async getConfig(): Promise<IqrfGatewayDaemonConfig> {
		const response: AxiosResponse<IqrfGatewayDaemonConfig> = await this.axiosInstance.get('/config/daemon');
		return response.data;
	}

	/**
	 * Retrieves IQRF Gateway Daemon component configuration and instances
	 * @template C Component name
	 * @param {C} component Component name
	 * @return {Promise<IqrfGatewayDaemonComponent<C>>} IQRF Gateway Daemon component configuration with instances
	 */
	public async getComponent<C extends IqrfGatewayDaemonComponentName>(
		component: C,
	): Promise<IqrfGatewayDaemonComponent<C>> {
		const response: AxiosResponse<IqrfGatewayDaemonComponent<C>> =
			await this.axiosInstance.get(`/config/daemon/${encodeURIComponent(component)}`);
		return response.data;
	}

	/**
	 * Creates new component instance
	 * @template C Component name
	 * @param {C} component Daemon component name
	 * @param {IqrfGatewayDaemonComponentInstanceConfiguration<C>} configuration Daemon component instance configuration
	 */
	public async createInstance<C extends IqrfGatewayDaemonComponentName>(
		component: C,
		configuration: IqrfGatewayDaemonComponentInstanceConfiguration<C>,
	): Promise<void> {
		await this.axiosInstance.post(`/config/daemon/${encodeURIComponent(component)}`, configuration);
	}

	/**
	 * Updates the component instance configuration
	 * @template C Component name
	 * @param {C} component Daemon component name
	 * @param {string} instance Daemon component instance name
	 * @param {IqrfGatewayDaemonComponentInstanceConfiguration<C>} configuration Daemon component instance configuration
	 */
	public async updateInstance<C extends IqrfGatewayDaemonComponentName>(
		component: C,
		instance: string,
		configuration: IqrfGatewayDaemonComponentInstanceConfiguration<C>,
	): Promise<void> {
		await this.axiosInstance.put(`/config/daemon/${encodeURIComponent(component)}/${encodeURIComponent(instance)}`, configuration);
	}

	/**
	 * Deletes component instance
	 * @template C Component name
	 * @param {C} component Daemon component name
	 * @param {string} instance Daemon component instance name
	 */
	public async deleteInstance<C extends IqrfGatewayDaemonComponentName>(
		component: C,
		instance: string,
	): Promise<void> {
		await this.axiosInstance.delete(`/config/daemon/${encodeURIComponent(component)}/${encodeURIComponent(instance)}`);
	}

	/**
	 * Updates component(s) state
	 * @param {IqrfGatewayDaemonComponentState[]} components Component state configuration
	 */
	public async updateEnabledComponents(components: IqrfGatewayDaemonComponentState[]): Promise<void> {
		await this.axiosInstance.patch('/config/daemon/components', components);
	}

	/**
	 * Lists IQRF Gateway Daemon mappings
	 * @param {MappingType | null} interfaceType Mapping interface type
	 * @return {Promise<IqrfGatewayDaemonMapping[]>} IQRF Gateway Daemon mappings
	 */
	public async listMappings(interfaceType: MappingType | null = null): Promise<IqrfGatewayDaemonMapping[]> {
		const params = interfaceType ? { interface: interfaceType } : {};
		const response: AxiosResponse<IqrfGatewayDaemonMapping[]> =
			await this.axiosInstance.get('/config/daemon/mappings', { params: params });
		return response.data;
	}

	/**
	 * Retrieves IQRF Gateway Daemon mapping
	 * @param {number} id IQRF Gateway Daemon mapping ID
	 * @return {Promise<IqrfGatewayDaemonMapping>} IQRF Gateway Daemon mapping
	 */
	public async getMapping(id: number): Promise<IqrfGatewayDaemonMapping> {
		const response: AxiosResponse<IqrfGatewayDaemonMapping> =
			await this.axiosInstance.get(`/config/daemon/mappings/${id.toString()}`);
		return response.data;
	}

	/**
	 * Creates IQRF Gateway Daemon mapping
	 * @param {IqrfGatewayDaemonMapping} mapping IQRF Gateway Daemon mapping
	 */
	public async createMapping(mapping: IqrfGatewayDaemonMapping): Promise<void> {
		delete mapping.id;
		await this.axiosInstance.post('/config/daemon/mappings', mapping);
	}

	/**
	 * Updates IQRF Gateway Daemon mapping
	 * @param {number} id IQRF Gateway Daemon mapping ID
	 * @param {IqrfGatewayDaemonMapping} mapping IQRF Gateway Daemon mapping
	 */
	public async updateMapping(id: number, mapping: IqrfGatewayDaemonMapping): Promise<void> {
		delete mapping.id;
		await this.axiosInstance.put(`/config/daemon/mappings/${id.toString()}`, mapping);
	}

	/**
	 * Deletes IQRF Gateway Daemon mapping
	 * @param {number} id IQRF Gateway Daemon mapping ID
	 */
	public async deleteMapping(id: number): Promise<void> {
		await this.axiosInstance.delete(`/config/daemon/mappings/${id.toString()}`);
	}

	/**
	 * Imports scheduler tasks
	 * @param {File} data Scheduler task JSON file or ZIP archive
	 */
	public async importScheduler(data: File): Promise<void> {
		await this.axiosInstance.post('/config/daemon/scheduler/import', data, { headers: { 'Content-Type': data.type } });
	}

	/**
	 * Exports scheduler tasks
	 * @return {Promise<FileResponse<Blob>>} Scheduler task ZIP archive
	 */
	public async exportScheduler(): Promise<FileResponse<Blob>> {
		const response: AxiosResponse<Blob> =
			await this.axiosInstance.get('/config/daemon/scheduler/export', { responseType: 'blob' });
		return FileResponse.fromAxiosResponse(response, 'iqrf-gateway-scheduler.zip');
	}

	/**
	 * Retrieves scheduler messaging instances
	 * @return {Promise<IqrfGatewayDaemonSchedulerMessagings>} Scheduler task suitable messaging instances
	 */
	public async getSchedulerMessagings(): Promise<IqrfGatewayDaemonSchedulerMessagings> {
		const response: AxiosResponse<IqrfGatewayDaemonSchedulerMessagings> =
			await this.axiosInstance.get('/config/daemon/scheduler/messagings');
		return response.data;
	}

}
