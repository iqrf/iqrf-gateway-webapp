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

import { type AxiosResponse } from 'axios';

import {
	type MenderConfig,
	type MenderRemount,
} from '../types/Config';

import { BaseService } from './BaseService';

/**
 * Mender service
 */
export class MenderService extends BaseService {

	/**
	 * Fetches Mender configuration
	 * @return {Promise<MenderConfig>} Mender configuration
	 */
	public async getConfig(): Promise<MenderConfig> {
		const response: AxiosResponse<MenderConfig> =
			await this.axiosInstance.get('/config/mender');
		return response.data;
	}

	/**
	 * Edits Mender configuration
	 * @param {MenderConfig} config Mender configuration
	 */
	public async editConfig(config: MenderConfig): Promise<void> {
		await this.axiosInstance.put('/config/mender', config);
	}

	/**
	 * Uploads Mender server certificate
	 * @param {File} certificate Certificate
	 * @return {Promise<string>} Path to uploaded certificate
	 */
	public async uploadCert(certificate: File): Promise<string> {
		const formData = new FormData();
		formData.append('certificate', certificate);
		const response: AxiosResponse<string> = await this.axiosInstance.post('/config/mender/cert', formData);
		return response.data;
	}

	/**
	 * Installs data from Mender artifact
	 * @param {File} artifact Mender artifact file
	 * @return {Promise<string>} Mender update output log
	 */
	public async install(artifact: File): Promise<string> {
		const formData = new FormData();
		formData.append('file', artifact);
		const response: AxiosResponse<string> = await this.axiosInstance.post('/mender/install');
		return response.data;
	}

	/**
	 * Commits installed artifact update
	 * @return {Promise<string>} Mender update output log
	 */
	public async commit(): Promise<string> {
		const response: AxiosResponse<string> = await this.axiosInstance.post('/mender/commit');
		return response.data;
	}

	/**
	 * Rolls installed artifact update back
	 * @return {Promise<string>} Mender update output log
	 */
	public async rollback(): Promise<string> {
		const response: AxiosResponse<string> = await this.axiosInstance.post('/mender/rollback');
		return response.data;
	}

	/**
	 * Remounts filesystem
	 * @param {MenderRemount} mode Mount mode
	 */
	public async remount(mode: MenderRemount): Promise<void> {
		await this.axiosInstance.post('/mender/remount', mode);
	}
}
