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

import { type AxiosResponse } from 'axios';

import { type AptConfig, type AptConfigRaw } from '../../types/Config/Apt';
import { BaseService } from '../BaseService';

/**
 * APT configuration service
 */
export class AptService extends BaseService {

	/**
	 * Fetches APT configuration
	 * @return {Promise<AptConfig>} APT configuration
	 */
	public getConfig(): Promise<AptConfig> {
		return this.axiosInstance.get('/config/apt')
			.then((response: AxiosResponse<AptConfigRaw>): AptConfig => {
				return this.fromRaw(response.data);
			});
	}

	/**
	 * Edits APT configuration
	 * @param {AptConfig} config APT configuration
	 */
	public editConfig(config: AptConfig): Promise<void> {
		const data = this.toRaw(config);
		return this.axiosInstance.put('/config/apt', data)
			.then((): void => {return;});
	}

	/**
	 * Changes service state of unattended upgrades service
	 * @param {boolean} enabled Service state
	 */
	public setServiceState(enabled: boolean): Promise<void> {
		const data = {
			'APT::Periodic::Enable': enabled ? '1': '0',
		};
		return this.axiosInstance.put('/config/apt', data)
			.then((): void => {return;});
	}

	/**
	 * Get APT configuration from raw APT configuration
	 * @param {AptConfigRaw} config Raw APT configuration
	 * @return {AptConfig} APT configuration
	 */
	private fromRaw(config: AptConfigRaw): AptConfig {
		return {
			enabled: config['APT::Periodic::Enable'] === '1',
			packageListUpdateInterval: parseInt(config['APT::Periodic::Update-Package-Lists']),
			packageUpdateInterval: parseInt(config['APT::Periodic::Unattended-Upgrade']),
			packageRemovalInterval: parseInt(config['APT::Periodic::AutocleanInterval']),
			rebootOnKernelUpdate: config['Unattended-Upgrade::Automatic-Reboot'] === 'true',
		};
	}

	/**
	 * Get raw APT configuration from APT configuration
	 * @param {AptConfig} config APT configuration
	 * @return {AptConfigRaw} Raw APT configuration
	 */
	private toRaw(config: AptConfig): AptConfigRaw {
		return {
			'APT::Periodic::Enable': config.enabled ? '1' : '0',
			'APT::Periodic::Update-Package-Lists': config.packageListUpdateInterval.toString(),
			'APT::Periodic::Unattended-Upgrade': config.packageUpdateInterval.toString(),
			'APT::Periodic::AutocleanInterval': config.packageRemovalInterval.toString(),
			'Unattended-Upgrade::Automatic-Reboot': config.rebootOnKernelUpdate ? 'true' : 'false',
		};
	}
}
