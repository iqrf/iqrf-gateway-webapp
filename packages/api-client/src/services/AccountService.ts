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
import {
	validate as uuidValidate,
	version as uuidVersion,
} from 'uuid';

import {
	type EmailSentResponse,
	type EmailVerificationResendRequest,
	type UserAccountRecovery,
	type UserEdit,
	type UserInfo,
	type UserPasswordChange,
	type UserPasswordReset,
	type UserSignedIn,
} from '../types';
import { UserUtils } from '../utils';

import { BaseService } from './BaseService';

/**
 * Account service
 */
export class AccountService extends BaseService {

	/**
	 * Fetches information about the logged-in user
	 * @return {Promise<UserInfo>} User information
	 */
	public async fetchInfo(): Promise<UserInfo> {
		const response: AxiosResponse<UserInfo> = await this.axiosInstance.get('/user');
		return UserUtils.deserialize(response.data);
	}

	/**
	 * Edits the user
	 * @param {UserEdit} user User to edit
	 * @return {Promise<EmailSentResponse>} Email sent response
	 */
	public async edit(user: UserEdit): Promise<EmailSentResponse> {
		const response: AxiosResponse<EmailSentResponse> =
			await this.axiosInstance.put('/user', UserUtils.serialize(user));
		return response.data;
	}

	/**
	 * Changes the user's password
	 * @param {UserPasswordChange} change Password change request
	 */
	public async changePassword(change: UserPasswordChange): Promise<void> {
		await this.axiosInstance.put('/user/password', change);
	}

	/**
	 * Resets the new user's password
	 * @param {string} requestUuid Password recovery request UUID
	 * @param {UserPasswordReset} request Password reset request
	 */
	public async confirmPasswordRecovery(requestUuid: string, request: UserPasswordReset): Promise<UserSignedIn> {
		if (!uuidValidate(requestUuid)) {
			throw new Error('Invalid password recovery request UUID.');
		}
		if (uuidVersion(requestUuid) !== 4) {
			throw new Error('Invalid password recovery request UUID version.');
		}
		const response: AxiosResponse<UserSignedIn> =
			await this.axiosInstance.post(`/user/password/recovery/${requestUuid}`, request);
		return UserUtils.deserialize(response.data);
	}

	/**
	 * Request user account recovery
	 * @param {UserAccountRecovery} recovery Account recovery request
	 */
	public async requestPasswordRecovery(recovery: UserAccountRecovery): Promise<void> {
		await this.axiosInstance.post('/user/password/recovery', recovery);
	}

	/**
	 * Resends the verification email
	 * @param {EmailVerificationResendRequest} request Verification e-mail resend request
	 */
	public async resendVerificationEmail(request: EmailVerificationResendRequest): Promise<void> {
		await this.axiosInstance.post('/user/resendVerification', request);
	}
}
