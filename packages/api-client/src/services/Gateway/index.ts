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

import { BaseService } from '../BaseService';

import { HostnameService } from './HostnameService';
import { InfoService } from './InfoService';
import { LogService } from './LogService';
import { PowerService } from './PowerService';
import { TimeService } from './TimeService';

export * from './HostnameService';
export * from './InfoService';
export * from './LogService';
export * from './PowerService';
export * from './TimeService';

/**
 * IQRF Gateway services
 */
export class GatewayServices extends BaseService {

	/**
	 * Returns hostname service
	 * @return {HostnameService} Hostname service
	 */
	public getHostnameService(): HostnameService {
		return new HostnameService(this.apiClient);
	}

	/**
	 * Returns info service
	 * @return {InfoService} Info service
	 */
	public getInfoService(): InfoService {
		return new InfoService(this.apiClient);
	}

	/**
	 * Returns log service
	 * @return {LogService} Log service
	 */
	public getLogService(): LogService {
		return new LogService(this.apiClient);
	}

	/**
	 * Returns power service
	 * @return {PowerService} Power service
	 */
	public getPowerService(): PowerService {
		return new PowerService(this.apiClient);
	}

	/**
	 * Returns time service
	 * @return {TimeService} Time service
	 */
	public getTimeService(): TimeService {
		return new TimeService(this.apiClient);
	}

}
