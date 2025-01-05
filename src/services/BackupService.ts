/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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
import axios, {AxiosResponse} from 'axios';
import {authorizationHeader} from '@/helpers/authorizationHeader';

import {IBackup} from '@/interfaces/Maintenance/Backup';

class BackupService {

	/**
	 * Retrieves gateway backup data
	 * @param params Backup parameters
	 */
	backup(params: IBackup): Promise<AxiosResponse> {
		return axios.post('maintenance/backup', params, {headers: authorizationHeader(), responseType: 'arraybuffer'});
	}

	/**
	 * Restores gateway from backup data
	 * @param archive Backup archive
	 */
	restore(archive: File): Promise<AxiosResponse> {
		const url = 'maintenance/restore';
		const headers = {
			...authorizationHeader(),
			'Content-Type': archive.type,
		};
		return axios.post(url, archive, {headers: headers, timeout: 120000});
	}
}

export default new BackupService();
