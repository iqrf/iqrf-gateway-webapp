/**
 * Copyright 2023-2025 MICRORISC s.r.o.
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
	type AccountEdit,
	type EmailSentResponse,
	type EmailVerificationResendRequest,
	type UserAccountRecovery,
	type UserCredentials,
	type UserInfo,
	type UserPasswordChange,
	type UserPasswordReset,
	type UserPreferences,
	type UserSignedIn,
} from '../types';
import { UserUtils } from '../utils';

import { BaseService } from './BaseService';

/**
 * Account service
 */
export class AccountService extends BaseService {

	/**
	 * Retrieve information about the logged-in user
	 * @return {Promise<UserInfo>} User information
	 */
	public async getInfo(): Promise<UserInfo> {
		const response: AxiosResponse<UserInfo> =
			await this.axiosInstance.get('/account');
		return UserUtils.deserialize(response.data);
	}

	/**
	 * Update the user
	 * @param {AccountEdit} user User to edit
	 * @return {Promise<EmailSentResponse>} Email sent response
	 */
	public async update(user: AccountEdit): Promise<EmailSentResponse> {
		const response: AxiosResponse<EmailSentResponse> =
			await this.axiosInstance.put('/account', UserUtils.serialize(user));
		return response.data;
	}

	/**
	 * Update the user's password
	 * @param {UserPasswordChange} change Password change request
	 */
	public async updatePassword(change: UserPasswordChange): Promise<void> {
		await this.axiosInstance.put('/account/password', change);
	}

	/**
	 * Reset the new user's password
	 * @param {string} requestUuid Password recovery request UUID
	 * @param {UserPasswordReset} request Password reset request
	 * @return {Promise<UserSignedIn>} Signed-in user
	 */
	public async confirmPasswordRecovery(requestUuid: string, request: UserPasswordReset): Promise<UserSignedIn> {
		if (!uuidValidate(requestUuid)) {
			throw new Error('Invalid password recovery request UUID.');
		}
		if (uuidVersion(requestUuid) !== 4) {
			throw new Error('Invalid password recovery request UUID version.');
		}
		const response: AxiosResponse<UserSignedIn> =
			await this.axiosInstance.post(`/account/passwordRecovery/${requestUuid}`, request);
		return UserUtils.deserialize(response.data);
	}

	/**
	 * Request user account recovery
	 * @param {UserAccountRecovery} recovery Account recovery request
	 */
	public async requestPasswordRecovery(recovery: UserAccountRecovery): Promise<void> {
		await this.axiosInstance.post('/account/passwordRecovery', recovery);
	}

	/**
	 * Retrieve preferences of the logged-in user
	 * @return {Promise<UserPreferences>} User preferences
	 */
	public async getPreferences(): Promise<UserPreferences> {
		const response: AxiosResponse<UserPreferences> =
			await this.axiosInstance.get('/account/preferences');
		return response.data;
	}

	/**
	 * Update the preferences of the logged-in user
	 * @param {UserPreferences} preferences User preferences
	 */
	public async updatePreferences(preferences: UserPreferences): Promise<void> {
		await this.axiosInstance.put('/account/preferences', preferences);
	}

	/**
	 * Verify the e-mail address
	 * @param {string} uuid E-mail verification UUID
	 * @return {Promise<UserSignedIn>} Signed-in user
	 */
	public async verifyEmail(uuid: string): Promise<UserSignedIn> {
		if (!uuidValidate(uuid)) {
			throw new Error('Invalid e-mail verification UUID.');
		}
		if (uuidVersion(uuid) !== 4) {
			throw new Error('Invalid e-mail verification UUID version.');
		}
		const response: AxiosResponse<UserSignedIn> =
			await this.axiosInstance.get(`/account/emailVerification/${uuid}`);
		return UserUtils.deserialize(response.data);
	}

	/**
	 * Resends the verification email
	 * @param {EmailVerificationResendRequest} request Verification e-mail resend request
	 */
	public async resendVerificationEmail(request: EmailVerificationResendRequest): Promise<void> {
		await this.axiosInstance.post('/account/emailVerification/resend', request);
	}

	/**
	 * Sign in the user
	 * @param {UserCredentials} credentials User credentials
	 * @return {Promise<UserSignedIn>} Signed in user
	 */
	public async signIn(credentials: UserCredentials): Promise<UserSignedIn> {
		const response: AxiosResponse<UserSignedIn> =
			await this.axiosInstance.post('/account/signIn', credentials);
		return UserUtils.deserialize(response.data);
	}

	/**
	 * Refresh the user token
	 * @return {Promise<UserSignedIn>} Signed in user
	 */
	public async refreshToken(): Promise<UserSignedIn> {
		const response: AxiosResponse<UserSignedIn> =
			await this.axiosInstance.post('/account/tokenRefresh');
		return UserUtils.deserialize(response.data);
	}


}
