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

import { type Modem } from '../../types/Network/Modem';
import { BaseService } from '../BaseService';

/**
 * Modem service
 */
export class ModemService extends BaseService {

	/**
	 * Lists available modems
	 * @return {Promise<Modem[]>} List of modems
	 */
	public async list(): Promise<Modem[]> {
		const response: AxiosResponse<Modem[]> =
			await this.axiosInstance.get('/network/gsm/modems');
		return response.data;
	}

	/**
	 * Scans for available modems
	 */
	public async scan(): Promise<void> {
		await this.axiosInstance.post('/network/gsm/modems/scan');
	}

}
