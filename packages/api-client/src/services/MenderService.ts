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

import { BaseService } from './BaseService';
import { type MenderRemount, type MenderConfig } from '../types/Config';

/**
 * Mender service
 */
export class MenderService extends BaseService {

	/**
	 * Fetches Mender configuration
	 * @return {Promise<MenderConfig>} Mender configuration
	 */
	public getConfig(): Promise<MenderConfig> {
		return this.axiosInstance.get('/config/mender')
			.then((response: AxiosResponse<MenderConfig>): MenderConfig => response.data);
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
	 */
	public install(artifact: File): Promise<void> {
		const formData = new FormData();
		formData.append('file', artifact);
		return this.axiosInstance.post('/mender/install')
			.then((): void => {return;});
	}

	/**
	 * Commits installed artifact update
	 */
	public commit(): Promise<void> {
		return this.axiosInstance.post('/mender/commit')
			.then((): void => {return;});
	}

	/**
	 * Rolls installed artifact update back
	 */
	public rollback(): Promise<void> {
		return this.axiosInstance.post('/mender/rollback')
			.then((): void => {return;});
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
