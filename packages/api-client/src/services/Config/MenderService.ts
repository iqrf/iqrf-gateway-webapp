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

import { type MenderConfig } from '../../types/Config';
import { BaseService } from '../BaseService';

/**
 * Mender service
 */
export class MenderService extends BaseService {

	/**
	 * Retrieves Mender configuration
	 * @return {Promise<MenderConfig>} Mender configuration
	 */
	public async getConfig(): Promise<MenderConfig> {
		const response: AxiosResponse<MenderConfig> =
			await this.axiosInstance.get('/config/mender');
		return response.data;
	}

	/**
	 * Updates Mender configuration
	 * @param {MenderConfig} config Mender configuration
	 */
	public async updateConfig(config: MenderConfig): Promise<void> {
		await this.axiosInstance.put('/config/mender', config);
	}

	/**
	 * Uploads Mender server certificate
	 * @param {File} certificate Certificate
	 * @return {Promise<string>} Path to uploaded certificate
	 */
	public async uploadCert(certificate: File): Promise<string> {
		const formData: FormData = new FormData();
		formData.append('certificate', certificate);
		const response: AxiosResponse<string> = await this.axiosInstance.post('/config/mender/cert', formData);
		return response.data;
	}

}
