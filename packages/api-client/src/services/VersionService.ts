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

import type {AxiosResponse} from 'axios';

import {BaseService} from './BaseService';
import type {VersionBase, VersionIqrfGatewayWebapp} from '../types';

/**
 * Version service
 */
export class VersionService extends BaseService {

	/**
	 * Fetches IQRF Gateway Daemon version
	 * @return {Promise<VersionBase>} IQRF Gateway Daemon version
	 */
	public getDaemon(): Promise<VersionBase> {
		return this.axiosInstance.get('/version/daemon')
			.then((response: AxiosResponse<VersionBase>): VersionBase => response.data);
	}

	/**
	 * Fetches IQRF Gateway Webapp version
	 * @return {Promise<VersionIqrfGatewayWebapp>} IQRF Gateway Webapp version
	 */
	public getWebapp(): Promise<VersionIqrfGatewayWebapp> {
		return this.axiosInstance.get('/version/webapp')
			.then((response: AxiosResponse<VersionIqrfGatewayWebapp>): VersionIqrfGatewayWebapp => response.data);
	}

}
