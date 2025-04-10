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

import { type AptConfig, type AptConfigRaw } from '../../types/Config';
import { BaseService } from '../BaseService';

/**
 * APT configuration service
 */
export class AptService extends BaseService {

	/**
	 * Retrieves APT configuration
	 * @return {Promise<AptConfig>} APT configuration
	 */
	public async getConfig(): Promise<AptConfig> {
		const response: AxiosResponse<AptConfigRaw> = await this.axiosInstance.get('/config/apt');
		return this.deserializeConfig(response.data);
	}

	/**
	 * Updates APT configuration
	 * @param {AptConfig} config New APT configuration
	 */
	public async updateConfig(config: AptConfig): Promise<void> {
		const data: AptConfigRaw = this.serializeConfig(config);
		await this.axiosInstance.put('/config/apt', data);
	}

	/**
	 * Updates service state of unattended upgrades service
	 * @param {boolean} enabled Service state
	 */
	public async updateServiceState(enabled: boolean): Promise<void> {
		const data = {
			'APT::Periodic::Enable': enabled ? '1' : '0',
		};
		await this.axiosInstance.put('/config/apt', data);
	}

	/**
	 * Deserializes APT configuration from raw APT configuration
	 * @param {AptConfigRaw} config Raw APT configuration
	 * @return {AptConfig} APT configuration
	 */
	private deserializeConfig(config: AptConfigRaw): AptConfig {
		return {
			enabled: config['APT::Periodic::Enable'] === '1',
			packageListUpdateInterval: Number.parseInt(config['APT::Periodic::Update-Package-Lists']),
			packageUpdateInterval: Number.parseInt(config['APT::Periodic::Unattended-Upgrade']),
			packageRemovalInterval: Number.parseInt(config['APT::Periodic::AutocleanInterval']),
			rebootOnKernelUpdate: config['Unattended-Upgrade::Automatic-Reboot'] === 'true',
		};
	}

	/**
	 * Serializes raw APT configuration from APT configuration
	 * @param {AptConfig} config APT configuration
	 * @return {AptConfigRaw} Raw APT configuration
	 */
	private serializeConfig(config: AptConfig): AptConfigRaw {
		return {
			'APT::Periodic::Enable': config.enabled ? '1' : '0',
			'APT::Periodic::Update-Package-Lists': config.packageListUpdateInterval.toString(),
			'APT::Periodic::Unattended-Upgrade': config.packageUpdateInterval.toString(),
			'APT::Periodic::AutocleanInterval': config.packageRemovalInterval.toString(),
			'Unattended-Upgrade::Automatic-Reboot': config.rebootOnKernelUpdate ? 'true' : 'false',
		};
	}

}
