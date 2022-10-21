/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
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
import {IControllerPinConfig} from '@/interfaces/Config/Controller';

/**
 * Controller pin configuration service
 */
class ControllerPinConfigService {
	/**
	 * Lists existing controller pin configurations
	 * @returns {Promise<AxiosResponse>} Response promise
	 */
	list(): Promise<AxiosResponse> {
		return axios.get('config/controller/pins', {headers: authorizationHeader()});
	}

	/**
	 * Retrieves a controller pin configuration profile
	 * @param id Profile ID
	 * @returns {Promise<AxiosResponse>} Response promise
	 */
	get(id: number): Promise<AxiosResponse> {
		return axios.get('config/controller/pins' + id, {headers: authorizationHeader()});
	}

	/**
	 * Adds a new controller pin configuration profile
	 * @param profile Profile configuration
	 * @returns {Promise<AxiosResponse>} Response promise
	 */
	add(profile: IControllerPinConfig): Promise<AxiosResponse> {
		return axios.post('config/controller/pins', profile, {headers: authorizationHeader()});
	}

	/**
	 * Edits an existing controller pin configuration profile
	 * @param id Profile ID
	 * @param profile Profile configuration
	 * @returns {Promise<AxiosResponse>} Response promise
	 */
	edit(id: number, profile: IControllerPinConfig): Promise<AxiosResponse> {
		return axios.put('config/controller/pins/' + id, profile, {headers: authorizationHeader()});
	}

	/**
	 * Deletes controller pin configuration profile specified by ID
	 * @param id Profile ID
	 * @returns {Promise<AxiosResponse>} Response promise
	 */
	delete(id: number): Promise<AxiosResponse> {
		return axios.delete('config/controller/pins/' + id, {headers: authorizationHeader()});
	}
}

export default new ControllerPinConfigService();
