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
import { DateTime } from 'luxon';

import { FileResponse } from '../../types';
import {
	type PowerActionResponse,
	type PowerActionResponseRaw,
} from '../../types/Gateway';
import { type GatewayBackup } from '../../types/Maintenance';
import { BaseService } from '../BaseService';

/**
 * Gateway backup and restore service
 */
export class BackupService extends BaseService {

	/**
	 * Backups gateway to archive
	 * @param {GatewayBackup} params Backup parameters
	 * @return {Promise<FileResponse<Blob>>} Backup archive
	 */
	public async backup(params: GatewayBackup): Promise<FileResponse<Blob>> {
		const response: AxiosResponse<Blob> =
			await this.axiosInstance.post('/maintenance/backup', params, { responseType: 'blob' });
		return FileResponse.fromAxiosResponse(response);
	}

	/**
	 * Restores gateway from archive
	 * @param {File} archive Backup archive
	 * @return {Promise<PowerActionResponse>} Power action response
	 */
	public async restore(archive: File): Promise<PowerActionResponse> {
		const response: AxiosResponse<PowerActionResponseRaw> =
			await this.axiosInstance.post('/maintenance/restore', archive, {
				headers: { 'Content-Type': archive.type },
				timeout: 120_000,
			});
		return {
			timestamp: DateTime.fromSeconds(response.data.timestamp),
		};
	}

}
