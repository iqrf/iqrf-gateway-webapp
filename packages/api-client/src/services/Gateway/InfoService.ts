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

import type { AxiosResponse } from 'axios';

import { BaseService } from '../BaseService';
import {type GatewayBriefInformation, type GatewayInformation} from '../../types/Gateway';

export class InfoService extends BaseService {

	/**
	 * Fetches brief information about the gateway
	 * @return {Promise<GatewayBriefInformation>} Brief information about the gateway
	 */
	public fetchBrief(): Promise<GatewayBriefInformation> {
		return this.apiClient.getAxiosInstance().get('/gateway/info/brief')
			.then((response: AxiosResponse<GatewayBriefInformation>): GatewayBriefInformation => response.data);
	}

	/**
	 * Fetches detailed information about the gateway
	 * @return {Promise<GatewayInformation>} Detailed information about the gateway
	 */
	public fetchDetailed(): Promise<GatewayInformation> {
		return this.apiClient.getAxiosInstance().get('/gateway/info')
			.then((response: AxiosResponse<GatewayInformation>): GatewayInformation => response.data);
	}

}