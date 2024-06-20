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

import { type MenderRemount, type MenderConfig } from '../types/Config';

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
	public editConfig(config: MenderConfig): Promise<void> {
		return this.axiosInstance.put('/config/mender', config)
			.then((): void => {return;});
	}

	/**
	 * Uploads Mender server certificate
	 * @param {File} certificate Certificate
	 * @return {Promise<string>} Path to uploaded certificate
	 */
	public uploadCert(certificate: File): Promise<string> {
		const formData = new FormData();
		formData.append('certificate', certificate);
		return this.axiosInstance.post('/config/mender/cert', formData)
			.then((response: AxiosResponse<string>): string => response.data);
	}

	/**
	 * Installs data from Mender artifact
	 * @param {File} artifact Mender artifact file
	 * @return {Promise<string>} Mender update output log
	 */
	public install(artifact: File): Promise<string> {
		const formData = new FormData();
		formData.append('file', artifact);
		return this.axiosInstance.post('/mender/install')
			.then((response: AxiosResponse<string>): string => response.data);
	}

	/**
	 * Commits installed artifact update
	 * @return {Promise<string>} Mender update output log
	 */
	public commit(): Promise<string> {
		return this.axiosInstance.post('/mender/commit')
			.then((response: AxiosResponse<string>): string => response.data);
	}

	/**
	 * Rolls installed artifact update back
	 * @return {Promise<string>} Mender update output log
	 */
	public rollback(): Promise<string> {
		return this.axiosInstance.post('/mender/rollback')
			.then((response: AxiosResponse<string>): string => response.data);
	}

	/**
	 * Remounts filesystem
	 * @param {MenderRemount} mode Mount mode
	 */
	public remount(mode: MenderRemount): Promise<void> {
		return this.axiosInstance.post('/mender/remount', mode)
			.then((): void => {return;});
	}
}
