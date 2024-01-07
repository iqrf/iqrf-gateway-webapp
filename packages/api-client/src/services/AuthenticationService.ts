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

import type {AxiosResponse} from 'axios';
import * as uuid from 'uuid';

import {BaseService} from './BaseService';

import type {UserCredentials, UserSignedIn} from '../types';
import {UserUtils} from '../utils';

/**
 * Authentication service
 */
export class AuthenticationService extends BaseService {

	/**
	 * Signs in the user
	 * @param {UserCredentials} credentials User credentials
	 * @return {Promise<UserSignedIn>} Signed in user
	 */
	public signIn(credentials: UserCredentials): Promise<UserSignedIn> {
		return this.axiosInstance.post('/user/signIn', credentials)
			.then((response: AxiosResponse<UserSignedIn>) => UserUtils.deserialize(response.data));
	}

	/**
	 * Refreshes the user token
	 * @return {Promise<UserSignedIn>} Signed in user
	 */
	public refreshToken(): Promise<UserSignedIn> {
		return this.axiosInstance.post('/user/refreshToken')
			.then((response: AxiosResponse<UserSignedIn>) => UserUtils.deserialize(response.data));
	}

	/**
	 * Verifies the user
	 * @param {string} verificationUuid Verification UUID
	 * @return {Promise<UserSignedIn>} Signed in user
	 */
	public verify(verificationUuid: string): Promise<UserSignedIn> {
		if (!uuid.validate(verificationUuid)) {
			throw new Error('Invalid verification UUID.');
		}
		if (uuid.version(verificationUuid) !== 4) {
			throw new Error('Invalid verification UUID version.');
		}
		return this.axiosInstance.get(`/user/verify/${verificationUuid}`)
			.then((response: AxiosResponse<UserSignedIn>) => UserUtils.deserialize(response.data));
	}

}
