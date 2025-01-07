/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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

/**
 * Daemon configuration service
 */
class DaemonConfigurationService {

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
	 * Deletes the component instance
	 * @param component Daemon component name
	 * @param instance Daemon component instance name
	 */
	deleteInstance(component: string, instance: string): Promise<AxiosResponse> {
		const url = 'config/daemon/' + encodeURIComponent(component) + '/' + encodeURIComponent(instance);
		return axios.delete(url, {headers: authorizationHeader()});
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

}

export default new DaemonConfigurationService();
