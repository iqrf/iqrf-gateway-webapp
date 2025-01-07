/**
 * Copyright 2023-2025 MICRORISC s.r.o.
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

import { type MenderRemount } from '../../types/Maintenance';
import { BaseService } from '../BaseService';

/**
 * Mender service
 */
export class MenderService extends BaseService {

	/**
	 * Installs data from Mender artifact
	 * @param {File} artifact Mender artifact file
	 * @return {Promise<string>} Mender update output log
	 */
	public async install(artifact: File): Promise<string> {
		const formData = new FormData();
		formData.append('file', artifact);
		const response: AxiosResponse<string> =
			await this.axiosInstance.post('/mender/install');
		return response.data;
	}

	/**
	 * Commits installed artifact update
	 * @return {Promise<string>} Mender update output log
	 */
	public async commit(): Promise<string> {
		const response: AxiosResponse<string> =
			await this.axiosInstance.post('/mender/commit');
		return response.data;
	}

	/**
	 * Rolls installed artifact update back
	 * @return {Promise<string>} Mender update output log
	 */
	public async rollback(): Promise<string> {
		const response: AxiosResponse<string> =
			await this.axiosInstance.post('/mender/rollback');
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
