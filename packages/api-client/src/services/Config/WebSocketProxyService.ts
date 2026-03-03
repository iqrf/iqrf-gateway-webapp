/**
 * Copyright 2023-2026 MICRORISC s.r.o.
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

import { type WebSocketProxyConfig } from '../../types/Config/WebSocketProxy';
import { BaseService } from '../BaseService';

/**
 * WebSocket proxy server service
 */
export class WebSocketProxyService extends BaseService {

	/**
	 * Retrieves WebSocket proxy server configuration
	 * @return {Promise<WebSocketProxyConfig>} WebSocket proxy server configuration
	 */
	public async getConfig(): Promise<WebSocketProxyConfig> {
		const response: AxiosResponse<WebSocketProxyConfig> =
			await this.axiosInstance.get('/config/ws-proxy');
		return response.data;
	}

	/**
	 * Updates WebSocket proxy server configuration
	 * @param {WebSocketProxyConfig} config WebSocket proxy server configuration
	 */
	public async updateConfig(config: WebSocketProxyConfig): Promise<void> {
		await this.axiosInstance.put('/config/ws-proxy', config);
	}

}
