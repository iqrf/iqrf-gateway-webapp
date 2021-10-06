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
import store from '../store';
import {authorizationHeader} from '../helpers/authorizationHeader';
import {WebSocketOptions} from '../store/modules/daemonClient.module';
import axios from 'axios';
import {AxiosResponse} from 'axios';

/**
 * Version service
 */
class VersionService {

	/**
	 * Retrieves IQRF Gateway Daemon version
	 * @param options WebSocket request options
	 */
	getDaemonVersion(options: WebSocketOptions): Promise<string> {
		options.request = {
			'mType': 'mngDaemon_Version',
			'data': {
				'returnVerbose': true,
			},
		};
		return store.dispatch('daemon_sendRequest', options);
	}

	/**
	 * Retrieves IQRF Gateway Daemon version via the REST API
	 */
	getDaemonVersionRest(): Promise<AxiosResponse> {
		return axios.get('/version/daemon', {headers: authorizationHeader()});
	}

	/**
	 * Retrieves IQRF Gateway Webapp version via the REST API
	 */
	getWebappVersionRest(): Promise <AxiosResponse> {
		return axios.get('version/webapp', {headers: authorizationHeader()});
	}
}

export default new VersionService();
