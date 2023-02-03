/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
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
import axios, {AxiosResponse} from 'axios';
import {authorizationHeader} from '@/helpers/authorizationHeader';
import {IChangeComponent} from '@/interfaces/Config/Daemon';

/**
 * Daemon configuration service
 */
class DaemonConfigurationService {

	/**
	 * Creates a new component
	 * @param configuration Daemon component configuration
	 */
	createComponent(configuration: any): Promise<AxiosResponse> {
		const url = 'config/daemon/';
		return axios.post(url, configuration, {headers: authorizationHeader()});
	}

	/**
	 * Creates a new component instance
	 * @param component Daemon component name
	 * @param configuration Daemon component instance configuration
	 */
	createInstance(component: string, configuration: any): Promise<AxiosResponse> {
		const url = 'config/daemon/' + encodeURIComponent(component);
		return axios.post(url, configuration, {headers: authorizationHeader()});
	}

	/**
	 * Retrieves the component configuration and instances
	 * @param component Daemon component name
	 */
	getComponent(component: string): Promise<AxiosResponse> {
		const url = 'config/daemon/' + encodeURIComponent(component);
		return axios.get(url, {headers: authorizationHeader()});
	}

	/**
	 * Retrieves the component instance configuration and instances
	 * @param component Daemon component name
	 * @param instance Daemon component instance name
	 */
	getInstance(component: string, instance: string): Promise<AxiosResponse> {
		const url = 'config/daemon/' + encodeURIComponent(component) + '/' + encodeURIComponent(instance);
		return axios.get(url, {headers: authorizationHeader()});
	}

	/**
	 * Deletes the component
	 * @param component Daemon component name
	 */
	deleteComponent(component: string): Promise<AxiosResponse> {
		const url = 'config/daemon/' + encodeURIComponent(component);
		return axios.delete(url, {headers: authorizationHeader()});
	}

	/**
	 * Deletes the component instance
	 * @param component Daemon component name
	 * @param instance Daemon component instance name
	 */
	deleteInstance(component: string, instance: string): Promise<AxiosResponse> {
		const url = 'config/daemon/' + encodeURIComponent(component) + '/' + encodeURIComponent(instance);
		return axios.delete(url, {headers: authorizationHeader()});
	}

	/**
	 * Exports daemon configuration
	 */
	export(): Promise<AxiosResponse> {
		const url = 'config/daemon/migration/export';
		return axios.get(url, {headers: authorizationHeader(), responseType: 'arraybuffer'});
	}

	/**
	 * Imports daemon configuration
	 * @param config daemon configuration
	 */
	import(config: File): Promise<AxiosResponse> {
		const url = 'config/daemon/migration/import';
		const headers = {
			...authorizationHeader(),
			'Content-Type': config.type
		};
		return axios.post(url, config, {headers: headers});
	}

	/**
	 * Updates the component configuration
	 * @param component Daemon component name
	 * @param configuration Daemon component configuration
	 */
	updateComponent(component: string, configuration: any): Promise<AxiosResponse> {
		const url = 'config/daemon/' + encodeURIComponent(component);
		return axios.put(url, configuration, {headers: authorizationHeader()});
	}

	/**
	 * Updates the component instance configuration
	 * @param component Daemon component name
	 * @param instance Daemon component instance name
	 * @param configuration Daemon component instance configuration
	 */
	updateInstance(component: string, instance: string, configuration: any): Promise<AxiosResponse> {
		const url = 'config/daemon/' + encodeURIComponent(component) + '/' + encodeURIComponent(instance);
		return axios.put(url, configuration, {headers: authorizationHeader()});
	}

	/**
	 * Changes IQRF component
	 * @param data component configuration
	 */
	changeComponent(data: Array<IChangeComponent>): Promise<AxiosResponse> {
		return axios.patch('config/daemon/components', data, {headers: authorizationHeader()});
	}

}

export default new DaemonConfigurationService();
