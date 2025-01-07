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

import {
	type AccessPoint,
} from '../../types/Network';
import { BaseService } from '../BaseService';

/**
 * Wi-Fi service
 */
export class WiFiService extends BaseService {

	/**
	 * Retrieves a list of Wi-Fi access points
	 * @return {Promise<AccessPoint[]>} List of Wi-Fi access points
	 */
	public async listAccessPoints(): Promise<AccessPoint[]> {
		const response: AxiosResponse<AccessPoint[]> =
			await this.axiosInstance.get('/network/wifi/list');
		return response.data;
	}

}
