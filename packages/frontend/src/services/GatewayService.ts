/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
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

import {IHostname} from '@/interfaces/Gateway/Information';

/**
 * Root password interface
 */
export interface RootPassword {
	password: string,
}

/**
 * Gateway service
 */
class GatewayService {
	/**
	 * Retrieves a ZIP archive with diagnostics data
	 */
	getDiagnosticsArchive(): Promise<AxiosResponse> {
		return axios.get('diagnostics', {headers: authorizationHeader(), responseType: 'blob'});
	}

	/**
	 * Retrieves available logs
	 */
	getAvailableLogs(): Promise<AxiosResponse> {
		return axios.get('gateway/logs', {headers: authorizationHeader()});
	}

	/**
	 * Retrieves a service log
	 * @param {string} service Service name
	 */
	getServiceLog(service: string): Promise<AxiosResponse> {
		return axios.get('gateway/logs/' + service, {headers: authorizationHeader(), timeout: 60000});
	}

	/**
	 * Retrieves a ZIP archive with IQRF Gateway Daemon's log files
	 */
	getLogArchive(): Promise<AxiosResponse> {
		return axios.get('gateway/logs/export', {headers: authorizationHeader(), responseType: 'blob'});
	}

	/**
	 * Performs power off
	 */
	performPowerOff(): Promise<AxiosResponse> {
		return axios.post('gateway/poweroff', null, {headers: authorizationHeader()});
	}

	/**
	 * Performs reboot
	 */
	performReboot(): Promise<AxiosResponse> {
		return axios.post('gateway/reboot', null, {headers: authorizationHeader()});
	}

	/**
	 * Sets default gateway user password
	 * @param {RootPassword} data New password
	 */
	setGatewayPassword(data: RootPassword): Promise<AxiosResponse> {
		return axios.put('gateway/password', data, {headers: authorizationHeader()});
	}

	/**
	 * Sets hostname
	 * @param {IHostname} config Hostname configuration
	 */
	setHostname(config: IHostname): Promise<AxiosResponse> {
		return axios.post('gateway/hostname', config, {headers: authorizationHeader()});
	}

}

export default new GatewayService();
