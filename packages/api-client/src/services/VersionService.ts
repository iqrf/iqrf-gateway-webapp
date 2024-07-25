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
	type VersionIqrfGatewayDaemon,
	type VersionIqrfGatewayWebapp,
} from '../types';

import { BaseService } from './BaseService';

/**
 * Version service
 */
export class VersionService extends BaseService {

	/**
	 * Retrieve IQRF Gateway Daemon version
	 * @return {Promise<VersionIqrfGatewayDaemon>} IQRF Gateway Daemon version
	 */
	public async getDaemon(): Promise<VersionIqrfGatewayDaemon> {
		const response: AxiosResponse<VersionIqrfGatewayDaemon> =
			await this.axiosInstance.get('/version/daemon');
		return response.data;
	}

	/**
	 * Retrieve IQRF Gateway Webapp version
	 * @return {Promise<VersionIqrfGatewayWebapp>} IQRF Gateway Webapp version
	 */
	public async getWebapp(): Promise<VersionIqrfGatewayWebapp> {
		const response: AxiosResponse<VersionIqrfGatewayWebapp> =
			await this.axiosInstance.get('/version/webapp');
		return response.data;
	}

}
