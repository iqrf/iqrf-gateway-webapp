/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
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
import {authorizationHeader} from '@/helpers/authorizationHeader';
import {UploadUtilFile} from '@/interfaces/trUpload';

interface IqrfInterfacePorts {
	cdc: Array<string>;
	spi: Array<string>;
	uart: Array<string>;
}

/**
 * IQRF networks service
 */
class IqrfService {

	/**
	 * Retrieves IQRF interface ports
	 */
	getInterfacePorts(interfaceType: string): Promise<Array<string>> {
		return axios.get('iqrf/interfaces/', {headers: authorizationHeader()})
			.then((response: AxiosResponse<any>) => {
				const ports: IqrfInterfacePorts = response.data;
				return ports[interfaceType] as Array<string>;
			});
	}

	/**
	 * Retrieves IQRF IDE Macros
	 */
	getMacros(): Promise<AxiosResponse> {
		return axios.get('iqrf/macros/', {headers: authorizationHeader()});
	}

	/**
	 * Retrieves IQRF OS patches
	 * @param {Record<string, number|string>} data API request body
	 */
	getUpgrades(data: Record<string, number|string>): Promise<AxiosResponse> {
		return axios.post('iqrf/osUpgrades', data, {headers: authorizationHeader()});
	}

	/**
	 * Retrieves IQRF OS and DPA upgrade file names
	 * @param {Record<string, number|string>} data API request body
	 */
	getUpgradeFiles(data: Record<string, number|string>): Promise<AxiosResponse> {
		return axios.post('iqrf/osUpgradeFiles', data, {headers: authorizationHeader()});
	}

	/**
	 * Executes upload via IQRF Gateway Uploader
	 * @param {Record<string, string>} data API request body
	 */
	uploader(data: UploadUtilFile): Promise<AxiosResponse<void>> {
		return axios.post('iqrf/uploader', data, {headers: authorizationHeader(), timeout: 30000});
	}

}

export default new IqrfService();
