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

import { type AxiosRequestConfig, type AxiosResponse } from 'axios';

import {
	type NetworkInterface,
	type NetworkInterfaceType,
} from '../../types/Network';
import { BaseService } from '../BaseService';

/**
 * Network interface service
 */
export class NetworkInterfaceService extends BaseService {

	/**
	 * Lists available network interfaces
	 * @param {NetworkInterfaceType} type Network interface type
	 * @return {Promise<NetworkInterface[]>} List of network interfaces
	 */
	public async list(type: NetworkInterfaceType | null = null): Promise<NetworkInterface[]> {
		const config: AxiosRequestConfig = {};
		if (type !== null) {
			Object.assign(config, { params: { type: type } });
		}
		const response: AxiosResponse<NetworkInterface[]> =
			await this.axiosInstance.get('/network/interfaces', config);
		return response.data;
	}

}
