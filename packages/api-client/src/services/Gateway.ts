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

import { BaseService } from './BaseService';
import { HostnameService } from './Gateway/HostnameService';
import { InfoService } from './Gateway/InfoService';
import { LogService } from './Gateway/LogService';
import { PowerService } from './Gateway/PowerService';
import { SshKeyService } from './Gateway/SshKeyService';
import { TimeService } from './Gateway/TimeService';

export * from './Gateway/HostnameService';
export * from './Gateway/InfoService';
export * from './Gateway/LogService';
export * from './Gateway/PowerService';
export * from './Gateway/SshKeyService';
export * from './Gateway/TimeService';

/**
 * IQRF Gateway services
 */
export class GatewayServices extends BaseService {

	/**
	 * Returns hostname service
	 * @returns {HostnameService} Hostname service
	 */
	public getHostnameService(): HostnameService {
		return new HostnameService(this.apiClient);
	}

	/**
	 * Returns info service
	 * @returns {InfoService} Info service
	 */
	public getInfoService(): InfoService {
		return new InfoService(this.apiClient);
	}

	/**
	 * Returns log service
	 * @returns {LogService} Log service
	 */
	public getLogService(): LogService {
		return new LogService(this.apiClient);
	}

	/**
	 * Returns power service
	 * @returns {PowerService} Power service
	 */
	public getPowerService(): PowerService {
		return new PowerService(this.apiClient);
	}

	/**
	 * Returns SSH key service
	 * @return {SshKeyService} SSH key service
	 */
	public getSshKeyService(): SshKeyService {
		return new SshKeyService(this.apiClient);
	}

	/**
	 * Returns time service
	 * @returns {TimeService} Time service
	 */
	public getTimeService(): TimeService {
		return new TimeService(this.apiClient);
	}

}
