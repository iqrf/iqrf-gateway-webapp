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

import { type PowerActionResponse } from '../../types/Gateway/Power';
import { type GatewayBackup } from '../../types/Maintenance/Backup';
import { BaseService } from '../BaseService';

/**
 * Gateway backup and restore service
 */
export class BackupService extends BaseService {

	public backup(params: GatewayBackup): Promise<ArrayBuffer> {
		return this.axiosInstance.post('/maintenance/backup', params, { responseType: 'arraybuffer' })
			.then((response: AxiosResponse<ArrayBuffer>) => response.data);
	}

	/**
	 * Restore gateway from archive
	 * @param {File} archive Backup archive
	 * @return {Promise<PowerActionResponse>}
	 */
	public restore(archive: File): Promise<PowerActionResponse> {
		return this.axiosInstance.post('/maintenance/restore', archive, { headers: { 'Content-Type': archive.type }, timeout: 120000 })
			.then((response: AxiosResponse<PowerActionResponse>): PowerActionResponse => response.data);
	}

}
