/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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
import axios, {AxiosResponse} from 'axios';
import { authorizationHeader } from '@/helpers/authorizationHeader';

/**
 * APT configuration
 */
export interface AptConfiguration {
	/**
	 * Enable automatic upgrades
	 */
	'APT::Periodic::Enable'?: boolean

	/**
	 * Package list update interval
	 */
	'APT::Periodic::Update-Package-Lists': number

	/**
	 * Package upgrade interval
	 */
	'APT::Periodic::Unattended-Upgrade': number

	/**
	 * Unnecessary package removal interval
	 */
	'APT::Periodic::AutocleanInterval': number

	/**
	 * Reboot on kernel updates
	 */
	'Unattended-Upgrade::Automatic-Reboot': boolean
}

/**
 * APT configuration service
 */
class AptService {

	/**
	 * Retrieves APT configuration
	 */
	read(): Promise<AptConfiguration> {
		return axios.get('/config/apt', {headers: authorizationHeader()})
			.then((response: AxiosResponse) => {
				const data = response.data;
				return {
					'APT::Periodic::AutocleanInterval': parseInt(data['APT::Periodic::AutocleanInterval']),
					'APT::Periodic::Enable': data['APT::Periodic::Enable'] === '1',
					'APT::Periodic::Unattended-Upgrade': parseInt(data['APT::Periodic::Unattended-Upgrade']),
					'APT::Periodic::Update-Package-Lists': parseInt(data['APT::Periodic::Update-Package-Lists']),
					'Unattended-Upgrade::Automatic-Reboot': data['Unattended-Upgrade::Automatic-Reboot'] === 'true',
				};
			});
	}

	/**
	 * Sets APT configuration
	 * @param {AptConfiguration} configuration APT configuration
	 */
	write(configuration: AptConfiguration): Promise<AxiosResponse> {
		const data = {
			'APT::Periodic::AutocleanInterval': configuration['APT::Periodic::AutocleanInterval'].toString(),
			'APT::Periodic::Enable': configuration['APT::Periodic::Enable'] ? '1' : '0',
			'APT::Periodic::Unattended-Upgrade': configuration['APT::Periodic::Unattended-Upgrade'].toString(),
			'APT::Periodic::Update-Package-Lists': configuration['APT::Periodic::Update-Package-Lists'].toString(),
			'Unattended-Upgrade::Automatic-Reboot': configuration['Unattended-Upgrade::Automatic-Reboot'] ? 'true' : 'false',
		};
		return axios.put('/config/apt', data, {headers: authorizationHeader()});
	}

	/**
	 * Enables unattended upgrades
	 * @param {boolean} enable Enable unattended upgrades
	 */
	setUnattendedUpgrades(enable: boolean): Promise<AxiosResponse> {
		const data = {
			'APT::Periodic::Enable': enable ? '1' : '0',
		};
		return axios.put('/config/apt', data, {headers: authorizationHeader()});
	}
}

export default new AptService();
