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
import { DateTime, Duration } from 'luxon';

import {
	type GatewayUptime,
	type GatewayUptimeRaw,
	type PowerActionResponse,
} from '../../types/Gateway';
import { BaseService } from '../BaseService';

/**
 * Gateway power service
 */
export class PowerService extends BaseService {

	/**
	 * Performs shutdown
	 * @return {Promise<PowerActionResponse>} Gateway shutdown time
	 */
	public async powerOff(): Promise<PowerActionResponse> {
		const response: AxiosResponse<PowerActionResponse> =
			await this.axiosInstance.post('/gateway/power/poweroff');
		return response.data;
	}

	/**
	 * Performs reboot
	 * @return {Promise<PowerActionResponse>} Gateway reboot time
	 */
	public async reboot(): Promise<PowerActionResponse> {
		const response: AxiosResponse<PowerActionResponse> =
			await this.axiosInstance.post('/gateway/power/reboot');
		return response.data;
	}

	/**
	 * Retrieves gateway uptime stats
	 * @return {Promise<GatewayUptime[]>} Gateway uptime stats
	 */
	public async fetchStats(): Promise<GatewayUptime[]> {
		const response: AxiosResponse<GatewayUptimeRaw[]> =
			await this.axiosInstance.get('/gateway/power/stats');
		return response.data.map((uptime: GatewayUptimeRaw): GatewayUptime => ({
			...uptime,
			downtime: Duration.fromObject({ seconds: uptime.downtime }),
			running: Duration.fromObject({ seconds: uptime.running }),
			sleeping: Duration.fromObject({ seconds: uptime.sleeping }),
			shutdown: uptime.shutdown ? DateTime.fromISO(uptime.shutdown) : null,
			start: DateTime.fromISO(uptime.start),
		})).sort((a: GatewayUptime, b: GatewayUptime): number => b.id - a.id);
	}
}
