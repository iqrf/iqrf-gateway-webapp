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

import type {AxiosResponse} from 'axios';

import {BaseService} from '../BaseService';
import {
	type InterfacePorts,
	type IqrfInterfaceType,
} from '../../types/Iqrf';

/**
 * Interface ports service
 */
export class InterfacePortsService extends BaseService {

	/**
	 * Fetch interface ports
	 * @param {IqrfInterfaceType} interfaceType Interface type
	 * @return {Promise<string>} Inteface ports
	 */
	public getInterfacePorts(interfaceType: IqrfInterfaceType): Promise<string[]> {
		return this.axiosInstance.get('iqrf/interfaces/')
			.then((response: AxiosResponse<InterfacePorts>): string[] => {
				return response.data[interfaceType];
			});
	}

}
