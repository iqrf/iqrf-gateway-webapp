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

import {authorizationHeader} from '@/helpers/authorizationHeader';
import {AccessPoint} from '@iqrf/iqrf-gateway-webapp-client/types/Network/Wifi';

/**
 * Network connection service
 */
class NetworkConnectionService {

	/**
	 * Lists available wifi access points
	 * @return {Promise<AccessPoint[]>} WiFi access points
	 */
	public listWifiAccessPoints(): Promise<AccessPoint[]> {
		return axios.get('network/wifi/list', {headers: authorizationHeader()})
			.then((response: AxiosResponse<AccessPoint[]>) => (response.data));
	}

}

export default new NetworkConnectionService();
