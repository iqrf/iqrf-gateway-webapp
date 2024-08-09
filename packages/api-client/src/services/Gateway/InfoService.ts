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

import { FileResponse } from '../../types';
import {
	type GatewayBriefInformation,
	type GatewayInformation,
} from '../../types/Gateway';
import { BaseService } from '../BaseService';

/**
 * Gateway information service
 */
export class InfoService extends BaseService {

	/**
	 * Retrieves brief information about the gateway
	 * @return {Promise<GatewayBriefInformation>} Brief information about the gateway
	 */
	public async getBrief(): Promise<GatewayBriefInformation> {
		const response: AxiosResponse<GatewayBriefInformation> =
			await this.apiClient.getAxiosInstance().get('/gateway/info/brief');
		return response.data;
	}

	/**
	 * Retrieves detailed information about the gateway
	 * @return {Promise<GatewayInformation>} Detailed information about the gateway
	 */
	public async getDetailed(): Promise<GatewayInformation> {
		const response: AxiosResponse<GatewayInformation> =
			await this.apiClient.getAxiosInstance().get('/gateway/info');
		return response.data;
	}

	/**
	 * Retrieves diagnostics archive
	 * @return {Promise<FileResponse<Blob>>} Diagnostics archive
	 */
	public async getDiagnostics(): Promise<FileResponse<Blob>> {
		const response: AxiosResponse<Blob> =
			await this.apiClient.getAxiosInstance().get('/diagnostics', { responseType: 'blob' });
		return FileResponse.fromAxiosResponse(response);
	}

}
