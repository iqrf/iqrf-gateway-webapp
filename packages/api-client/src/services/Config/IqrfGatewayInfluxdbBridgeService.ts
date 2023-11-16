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

import type {AxiosResponse} from 'axios';

import {BaseService} from '../BaseService';

import type { BridgeConfig } from '../../types/Config';

/**
 * IQRF Gateway InfluxDB Bridge configuration service
 */
export class IqrfGatewayInfluxdbBridgeService extends BaseService {

	/**
	 * Fetches IQRF Gateway InfluxDB Bridge configuration
	 * @return {Promise<BridgeConfig>} IQRF Gateway InfluxDB Bridge configuration
	 */
	public fetchConfig(): Promise<BridgeConfig> {
		return this.axiosInstance.get('/config/bridge')
			.then((response: AxiosResponse<BridgeConfig>): BridgeConfig => response.data);
	}

	/**
	 * Edits IQRF Gateway InfluxDB Bridge configuration
	 * @param {BridgeConfig} config IQRF Gateway InfluxDB Bridge configuration
	 * @returns
	 */
	public editConfig(config: BridgeConfig): Promise<void> {
		return this.axiosInstance.put('/config/bridge', config)
			.then((): void => {return;});
	}

}
