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
import {IMonitConfig, MonitCheck} from '@/interfaces/Maintenance/Monit';

/**
 * Monit configuration service
 */
class MonitService {
	/**
	 * Retrieves Monit configuration
	 */
	getConfig(): Promise<AxiosResponse<IMonitConfig>> {
		return axios.get('config/monit', {headers: authorizationHeader()});
	}

	/**
	 * Saves new Monit configuration
	 * @param config new Monit configuration
	 */
	saveConfig(config: IMonitConfig): Promise<AxiosResponse> {
		return axios.put('config/monit', config, {headers: authorizationHeader()});
	}

	/**
	 * Retrieves Monit check
	 * @param {string} name Check name
	 */
	getCheck(name: string): Promise<AxiosResponse<MonitCheck>> {
		return axios.get('config/monit/checks/' + name, {headers: authorizationHeader()});
	}

	/**
	 * Enables Monit check
	 * @param {string} name Check name
	 */
	enableCheck(name: string): Promise<AxiosResponse> {
		return axios.post('config/monit/checks/' + name + '/enable', {}, {headers: authorizationHeader()});
	}

	/**
	 * Disables Monit check
	 * @param {string} name Check name
	 */
	disableCheck(name: string): Promise<AxiosResponse> {
		return axios.post('config/monit/checks/' + name + '/disable', {}, {headers: authorizationHeader()});
	}
}

export default new MonitService();
