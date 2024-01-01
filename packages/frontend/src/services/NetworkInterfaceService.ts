/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
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

import {InterfaceType} from '@/enums/Network/InterfaceType';
import {authorizationHeader} from '@/helpers/authorizationHeader';
import {NetworkInterface} from '@/interfaces/Network/Connection';
import {IModem} from '@/interfaces/Network/Mobile';

/**
 * Network interface service
 */
class NetworkInterfaceService {

	/**
	 * Lists available network interfaces
	 * @param type Network interface type
	 */
	public list(type: InterfaceType|null = null): Promise<Array<NetworkInterface>> {
		const config = {headers: authorizationHeader()};
		if (type !== null) {
			Object.assign(config, {params: {type: type}});
		}
		return axios.get('network/interfaces', config)
			.then((response: AxiosResponse) => {
				return response.data as Array<NetworkInterface>;
			});
	}

	/**
	 * Lists available modems
	 */
	public listModems(): Promise<Array<IModem>> {
		return axios.get('network/gsm/modems', {headers: authorizationHeader()})
			.then((response: AxiosResponse) => {
				return response.data as Array<IModem>;
			});
	}

	/**
	 * Scans for available modems
	 */
	public scanModems(): Promise<void> {
		return axios.post('network/gsm/modems/scan', {}, {headers: authorizationHeader()})
			.then(() => {return;});
	}

}

export default new NetworkInterfaceService();
