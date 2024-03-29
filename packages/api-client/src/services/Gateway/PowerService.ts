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
import type { AxiosResponse } from 'axios';

import { BaseService } from '../BaseService';
import { type PowerActionResponse } from '../../types/Gateway';

export class PowerService extends BaseService {

	/**
	 * Performs shutdown
	 * @returns {Promise<PowerActionResponse>} Gateway shutdown time
	 */
	public powerOff(): Promise<PowerActionResponse> {
		return this.axiosInstance.post('/gateway/power/poweroff')
			.then((response: AxiosResponse<PowerActionResponse>): PowerActionResponse => response.data);
	}

	/**
	 * Performs reboot
	 * @returns {Promise<PowerActionResponse>} Gateway reboot time
	 */
	public reboot(): Promise<PowerActionResponse> {
		return this.axiosInstance.post('/gateway/power/reboot')
			.then((response: AxiosResponse<PowerActionResponse>): PowerActionResponse => response.data);
	}
}
