/**
 * Copyright 2017-2022 IQRF Tech s.r.o.
 * Copyright 2019-2022 MICRORISC s.r.o.
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
 * APT enable
 */
export interface AptEnable {
	/**
	 * Enable automatic upgrades
	 */
	'APT::Periodic::Enable': string
}

export interface AptConfiguration {
	/**
	 * Enable automatic upgrades
	 */
	'APT::Periodic::Enable'?: string

	/**
	 * Package list update interval
	 */
	'APT::Periodic::Update-Package-Lists': string

	/**
	 * Package upgrade interval
	 */
	'APT::Periodic::Unattended-Upgrade': string

	/**
	 * Unnecessary package removal interval
	 */
	'APT::Periodic::AutocleanInterval': string

	/**
	 * Reboot on kernel updates
	 */
	'Unattended-Upgrade::Automatic-Reboot': string
}

/**
 * APT configuration service
 */
class AptService {

	/**
	 * Retrieves APT configuration
	 */
	read(): Promise<AxiosResponse> {
		return axios.get('/config/apt', {headers: authorizationHeader()});
	}

	/**
	 * Sets APT configuration
	 * @param configuration APT configuration
	 */
	write(configuration: AptEnable|AptConfiguration): Promise<AxiosResponse> {
		return axios.put('/config/apt', configuration, {headers: authorizationHeader()});
	}
}

export default new AptService();
