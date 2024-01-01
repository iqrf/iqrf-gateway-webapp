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
import {UserUtils} from '@iqrf/iqrf-gateway-webapp-client/dist/src/utils';
import {UserSignedIn} from '@iqrf/iqrf-gateway-webapp-client/types';
import axios, {AxiosResponse} from 'axios';

import {authorizationHeader} from '@/helpers/authorizationHeader';
import UrlBuilder from '@/helpers/urlBuilder';

/**
 * User service
 */
class UserService {

	/**
	 * Requests a password recovery
	 * @param {string} user User name
	 */
	requestPasswordRecovery(user: string): Promise<AxiosResponse> {
		const body = {
			baseUrl: new UrlBuilder().getBaseUrl(),
			username: user,
		};
		return axios.post('user/password/recovery', body, {headers: authorizationHeader()});
	}

	/**
	 * Sends a password change email
	 * @param {string} uuid Password recovery request UUID
	 * @param {string} password New password
	 */
	confirmPasswordRecovery(uuid: string, password: string): Promise<UserSignedIn> {
		const body = {
			password: password
		};
		return axios.post('user/password/recovery/' + uuid, body, {headers: authorizationHeader()})
			.then((response: AxiosResponse<UserSignedIn>) => UserUtils.deserialize(response.data));
	}

}

export default new UserService();
