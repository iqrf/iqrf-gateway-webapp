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

import { type DpaMacroGroup } from '../../types/Iqrf';
import { BaseService } from '../BaseService';

/**
 * DPA macro service
 */
export class DpaMacrosService extends BaseService {

	/**
	 * Retrieves all DPA macros
	 * @return {Promise<DpaMacroGroup[]>} DPA macros
	 */
	public async get(): Promise<DpaMacroGroup[]> {
		const response: AxiosResponse<DpaMacroGroup[]> = await this.axiosInstance.get('/iqrf/macros');
		return response.data;
	}

}
