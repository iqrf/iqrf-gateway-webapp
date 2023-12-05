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
import {
	type IqrfGatewayDaemonConfig,
	type IqrfGatewayDaemonComponent,
	type IqrfGatewayDaemonComponentInstanceConfiguration,
	type IqrfGatewayDaemonComponentName,
	type IqrfGatewayDaemonComponentState,
	type IqrfGatewayDaemonMapping,
	type IqrfGatewayDaemonSchedulerMessagings,
} from '../../types/Config';
import { type MappingType } from '../../types/Config/Mapping';

/**
 * IQRF Gateway Daemon configuration service
 */
export class IqrfGatewayDaemonService extends BaseService {

	/**
	 * Retrieves IQRF Gateway Daemon main configuration
	 * @return {Promise<IqrfGatewayDaemonConfig>} IQRF Gateway Daemon main configuration
	 */
	public getConfig(): Promise<IqrfGatewayDaemonConfig> {
		return this.axiosInstance.get('/config/daemon')
			.then((response: AxiosResponse<IqrfGatewayDaemonConfig>): IqrfGatewayDaemonConfig => response.data);
	}

	/**
	 * Retrieves IQRF Gateway Daemon component configuration and instances
	 * @template C Component name
	 * @param {C} component Component name
	 * @return {Promise<IqrfGatewayDaemonComponent<C>>} IQRF Gateway Daemon component configuration with instances
	 */
	public getComponent<C extends IqrfGatewayDaemonComponentName>(
		component: C,
	): Promise<IqrfGatewayDaemonComponent<C>> {
		return this.axiosInstance.get(`/config/daemon/${encodeURIComponent(component)}`)
			.then((response: AxiosResponse<IqrfGatewayDaemonComponent<C>>): IqrfGatewayDaemonComponent<C> => response.data);
	}

	/**
	 * Create new component instance
	 * @template C Component name
	 * @param {C} component Daemon component name
	 * @param {IqrfGatewayDaemonComponentInstanceConfiguration<C>} configuration Daemon component instance configuration
	 */
	public createInstance<C extends IqrfGatewayDaemonComponentName>(
		component: C,
		configuration: IqrfGatewayDaemonComponentInstanceConfiguration<C>,
	): Promise<void> {
		return this.axiosInstance.post(`/config/daemon/${encodeURIComponent(component)}`, configuration)
			.then((): void => {return;});
	}

	/**
	 * Updates the component instance configuration
	 * @template C Component name
	 * @param {C} component Daemon component name
	 * @param {string} instance Daemon component instance name
	 * @param {IqrfGatewayDaemonComponentInstanceConfiguration<C>} configuration Daemon component instance configuration
	 */
	public updateInstance<C extends IqrfGatewayDaemonComponentName>(
		component: C,
		instance: string,
		configuration: IqrfGatewayDaemonComponentInstanceConfiguration<C>,
	): Promise<void> {
		return this.axiosInstance.put(`/config/daemon/${encodeURIComponent(component)}/${encodeURIComponent(instance)}`, configuration)
			.then((): void => {return;});
	}

	/**
	 * Deletes component instance
	 * @template C Component name
	 * @param {C} component Daemon component name
	 * @param {string} instance Daemon component instance name
	 */
	public deleteInstance<C extends IqrfGatewayDaemonComponentName>(
		component: C,
		instance: string,
	): Promise<void> {
		return this.axiosInstance.delete(`/config/daemon/${encodeURIComponent(component)}/${encodeURIComponent(instance)}`)
			.then((): void => {return;});
	}

	/**
	 * Changed component(s) state
	 * @param {IqrfGatewayDaemonComponentState[]} components Component state configuration
	 */
	public changeEnabledComponents(components: IqrfGatewayDaemonComponentState[]): Promise<void> {
		return this.axiosInstance.patch('/config/daemon/components', components)
			.then((): void => {return;});
	}

	/**
	 * List IQRF Gateway Daemon mappings
	 * @return {Promise<IqrfGatewayDaemonMapping[]>} IQRF Gateway Daemon mappings
	 */
	public listMappings(interfaceType: MappingType | null = null): Promise<IqrfGatewayDaemonMapping[]> {
		const params = interfaceType ? {interface: interfaceType} : {};
		return this.axiosInstance.get('/mappings', {params: params})
			.then((response: AxiosResponse<IqrfGatewayDaemonMapping[]>) => response.data);
	}

	/**
	 * Fetch IQRF Gateway Daemon mapping
	 * @param {number} id IQRF Gateway Daemon mapping ID
	 * @return {Promise<IqrfGatewayDaemonMapping>} IQRF Gateway Daemon mapping
	 */
	public fetchMapping(id: number): Promise<IqrfGatewayDaemonMapping> {
		return this.axiosInstance.get(`/mappings/${id}`)
			.then((response: AxiosResponse<IqrfGatewayDaemonMapping>) => response.data);
	}

	/**
	 * Create IQRF Gateway Daemon mapping
	 * @param {IqrfGatewayDaemonMapping} mapping IQRF Gateway Daemon mapping
	 */
	public createMapping(mapping: IqrfGatewayDaemonMapping): Promise<void> {
		delete mapping.id;
		return this.axiosInstance.post('/mappings', mapping)
			.then((): void => {return;});
	}

	/**
	 * Edit IQRF Gateway Daemon mapping
	 * @param {number} id IQRF Gateway Daemon mapping ID
	 * @param {IqrfGatewayDaemonMapping} mapping IQRF Gateway Daemon mapping
	 */
	public editMapping(id: number, mapping: IqrfGatewayDaemonMapping): Promise<void> {
		delete mapping.id;
		return this.axiosInstance.put(`/mappings/${id}`, mapping)
			.then((): void => {return;});
	}

	/**
	 * Delete IQRF Gateway Daemon mapping
	 * @param {number} id IQRF Gateway Daemon mapping ID
	 */
	public deleteMapping(id: number): Promise<void> {
		return this.axiosInstance.delete(`/mappings/${id}`)
			.then((): void => {return;});
	}

	/**
	 * Import scheduler tasks
	 * @param {File} data Scheduler task JSON file or ZIP archive
	 */
	public schedulerImport(data: File): Promise<void> {
		return this.axiosInstance.post('/scheduler/import', data, {headers: {'Content-Type': data.type}})
			.then((): void => {return;});
	}

	/**
	 * Export scheduler tasks
	 * @return {Promise<ArrayBuffer>} Scheduler task ZIP archive
	 */
	public schedulerExport(): Promise<ArrayBuffer> {
		return this.axiosInstance.get('/scheduler/export', {responseType: 'arraybuffer'})
			.then((response: AxiosResponse<ArrayBuffer>) => response.data);
	}

	/**
	 * Fetch scheduler messaging instances
	 * @return {Promise<>} Scheduler task suitable messaging instances
	 */
	public schedulerMessagings(): Promise<IqrfGatewayDaemonSchedulerMessagings> {
		return this.axiosInstance.get('/scheduler/messagings')
			.then((response: AxiosResponse<IqrfGatewayDaemonSchedulerMessagings>): IqrfGatewayDaemonSchedulerMessagings => response.data);
	}

}
