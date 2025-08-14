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
 * Service status value object
 */
export interface ServiceStatus {

	/**
	 * Is the service enabled?
	 */
	enabled: boolean;

	/**
	 * Is the service active?
	 */
	active: boolean;

	/**
	 * Service status
	 */
	status: string;

}

/**
 * System service service
 */
class ServiceService {
	/**
	 * Disables the service
	 * @param name Service name
	 * @param stop Stop after disabling
	 */
	disable(name: string, stop: boolean | undefined = undefined): Promise<AxiosResponse> {
		const body = stop === undefined ? null : {stop: stop};
		return axios.post(
			'services/' + name + '/disable',
			body,
			{headers: authorizationHeader()}
		);
	}

	/**
	 * Enables the service
	 * @param name Service name
	 * @param start Start after enabling
	 */
	enable(name: string, start: boolean | undefined = undefined): Promise<AxiosResponse> {
		const body = start === undefined ? null : {start: start};
		console.warn(body);
		return axios.post(
			'services/' + name + '/enable',
			body,
			{headers: authorizationHeader()}
		);
	}

	/**
	 * Retrieves the service status
	 * @param name Service name
	 */
	getStatus(name: string): Promise<ServiceStatus> {
		return axios.get('services/' + name, {headers: authorizationHeader()})
			.then((response: AxiosResponse) => {
				return response.data as ServiceStatus;
			});
	}

	/**
	 * Restarts the service
	 * @param name Service name
	 */
	restart(name: string): Promise<AxiosResponse> {
		return axios.post('services/' + name + '/restart', null, {headers: authorizationHeader(), timeout: 90000});
	}

	/**
	 * Starts the service
	 * @param name Service name
	 */
	start(name: string): Promise<AxiosResponse> {
		return axios.post('services/' + name + '/start', null, {headers: authorizationHeader()});
	}

	/**
	 * Stops the service
	 * @param name Service name
	 */
	stop(name: string): Promise<AxiosResponse> {
		return axios.post('services/' + name + '/stop', null, {headers: authorizationHeader()});
	}
}

export default new ServiceService();
