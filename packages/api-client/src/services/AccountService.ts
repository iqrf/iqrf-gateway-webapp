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
import { type AxiosResponse } from 'axios';

import {BaseService} from './BaseService';

import {
	type EmailSentResponse,
	type UserAccountRecovery,
	type UserEdit,
	type UserInfo,
	type UserPasswordChange,
	type UserSignedIn,
} from '../types';
import {UserUtils} from '../utils';

/**
 * Account service
 */
export class AccountService extends BaseService {

	/**
	 * Fetches information about the logged in user
	 * @return {Promise<UserInfo>} User information
	 */
	public fetchInfo(): Promise<UserInfo> {
		return this.axiosInstance.get('/user')
			.then((response: AxiosResponse<UserInfo>) => response.data);
	}

	/**
	 * Edits the user
	 * @param {UserEdit} user User to edit
	 * @return {Promise<EmailSentResponse>} Email sent response
	 */
	public edit(user: UserEdit): Promise<EmailSentResponse> {
		return this.axiosInstance.put('/user', UserUtils.serialize(user))
			.then((response: AxiosResponse<EmailSentResponse>) => response.data);
	}

	/**
	 * Changes the user's password
	 * @param {UserPasswordChange} change Password change request
	 */
	public changePassword(change: UserPasswordChange): Promise<void> {
		return this.axiosInstance.put('/user/password', change)
			.then((): void => {return;});

	}

	/**
	 * Sets the new user's password
	 * @param {string} uuid Password recovery request UUID
	 * @param {string} password New password
	 */
	public confirmPasswordRecovery(uuid: string, password: string): Promise<UserSignedIn> {
		const body = {
			password: password,
		};
		return this.axiosInstance.post(`/user/password/recovery/${uuid}`, body)
			.then((response: AxiosResponse<UserSignedIn>) => UserUtils.deserialize(response.data));
	}

	/**
	 * Request user account recovery
	 * @param {UserAccountRecovery} recovery Account recovery request
	 */
	public requestPasswordRecovery(recovery: UserAccountRecovery): Promise<void> {
		return this.axiosInstance.post('/user/password/recovery/', recovery)
			.then((): void => {return;});
	}

	/**
	 * Resends the verification email
	 */
	public resendVerificationEmail(): Promise<void> {
		return this.axiosInstance.post('`/user/resendVerification')
			.then((): void => {return;});
	}
}
