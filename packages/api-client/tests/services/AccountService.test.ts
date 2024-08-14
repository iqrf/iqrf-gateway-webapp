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

import { beforeEach, describe, expect, it } from 'vitest';

import { AccountService } from '../../src/services';
import {
	AccountState,
	type EmailSentResponse,
	type EmailVerificationResendRequest,
	type UserAccountRecovery, type UserCredentials,
	type UserEdit,
	type UserInfo,
	UserLanguage,
	type UserPasswordChange,
	type UserPasswordReset,
	UserRole, UserSessionExpiration,
	type UserSignedIn,
} from '../../src/types';
import { mockedAxios, mockedClient } from '../mocks/axios';

describe('AccountService', (): void => {

	/**
	 * @var {UserCredentials} credentials User credentials
	 */
	const credentials: UserCredentials = {
		username: 'admin',
		password: 'iqrf',
	};

	/**
	 * @var {UserPasswordReset} passwordResetRequest Password reset request
	 */
	const passwordResetRequest: UserPasswordReset = {
		baseUrl: 'http://iqaros.local/',
		password: 'new-password',
	};

	/**
	 * @var {AccountService} service Account service
	 */
	const service: AccountService = new AccountService(mockedClient);

	/**
	 * @var {UserInfo} userInfo User information
	 */
	const userInfo: UserInfo = {
		id: 1,
		username: 'admin',
		email: 'admin@example.com',
		role: UserRole.Admin,
		language: UserLanguage.English,
		state: AccountState.Verified,
	};

	/**
	 * @var {UserSignedIn} userSignedIn User signed in
	 */
	const userSignedIn: UserSignedIn = {
		...userInfo,
		token: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJFUzM4NCJ9.eyJpYXQiOjE3MTA4Nzc3NjAsIm5iZiI6MTcxMDg3Nzc2MCwiZXhwIjoxNzEwODgzMTYwLCJ1aWQiOjEsImlzcyI6Ikxlbm92by1CNTEtODAiLCJqdGkiOiJMZW5vdm8tQjUxLTgwIn0._EguTP1nPp9N56tB40TrtXnuqKPZ3wlXERmvxiDtkHBzthJpQcwU7GkKgsIwL4f4I0LEPrmykZDmHlUSYG-BZiNPtGtFaiw_T5pC4FDYzUVLgitWg2rdKdKa5I7lmGuN',
	};

	beforeEach((): void => {
		mockedAxios.reset();
	});

	it('fetch information about the logged-in user', async (): Promise<void> => {
		expect.assertions(1);
		mockedAxios.onGet('/account')
			.reply(200, userInfo);
		const actual: UserInfo = await service.getInfo();
		expect(actual).toStrictEqual(userInfo);
	});

	it('edit the user', async (): Promise<void> => {
		expect.assertions(1);
		const request: UserEdit = {
			username: 'admin',
			email: 'admin@example.org',
			password: 'iqrf',
			language: UserLanguage.English,
			role: UserRole.Admin,
			baseUrl: 'http://iqaros.local/',
		};
		const response: EmailSentResponse = {
			emailSent: true,
		};
		mockedAxios.onPut('/account', request)
			.reply(200, response);
		const actual: EmailSentResponse = await service.update(request);
		expect(actual).toStrictEqual(response);
	});

	it('change user\' password', async (): Promise<void> => {
		expect.assertions(1);
		const request: UserPasswordChange = {
			old: 'iqrf',
			new: 'new-password',
			baseUrl: 'http://iqaros.local/',
		};
		mockedAxios.onPut('/account/password', request)
			.reply(200);
		await expect(service.updatePassword(request)).resolves.not.toThrow();
	});

	it('confirm password recovery - invalid UUID format', async (): Promise<void> => {
		expect.assertions(1);
		await expect(service.confirmPasswordRecovery('invalid-uuid', passwordResetRequest)).rejects
			.toThrow(new Error('Invalid password recovery request UUID.'));
	});

	it('confirm password recovery - invalid UUID version', async (): Promise<void> => {
		expect.assertions(1);
		await expect(service.confirmPasswordRecovery('60045219-7cbf-321e-a762-c90382cd8723', passwordResetRequest)).rejects
			.toThrow(new Error('Invalid password recovery request UUID version.'));
	});

	it('confirm password recovery', async (): Promise<void> => {
		expect.assertions(1);
		const uuid = '95b7edac-f3de-4dab-9cef-35a509b88f57';
		mockedAxios.onPost(`/account/passwordRecovery/${uuid}`, passwordResetRequest)
			.reply(200, userSignedIn);
		const actual: UserSignedIn = await service.confirmPasswordRecovery(uuid, passwordResetRequest);
		expect(actual).toStrictEqual(userSignedIn);
	});

	it('request password recovery', async (): Promise<void> => {
		expect.assertions(1);
		const request: UserAccountRecovery = {
			username: 'admin',
			baseUrl: 'http://iqaros.local/',
		};
		mockedAxios.onPost('/account/passwordRecovery', request)
			.reply(200);
		await expect(service.requestPasswordRecovery(request)).resolves.not.toThrow();
	});

	it('verify e-mail address - invalid UUID format', async (): Promise<void> => {
		expect.assertions(1);
		await expect(service.verifyEmail('invalid-uuid')).rejects
			.toThrow(new Error('Invalid e-mail verification UUID.'));
	});

	it('verify e-mail address - invalid UUID version', async (): Promise<void> => {
		expect.assertions(1);
		await expect(service.verifyEmail('60045219-7cbf-321e-a762-c90382cd8723')).rejects
			.toThrow(new Error('Invalid e-mail verification UUID version.'));
	});

	it('verify e-mail address', async (): Promise<void> => {
		expect.assertions(1);
		const uuid = '95b7edac-f3de-4dab-9cef-35a509b88f57';
		mockedAxios.onPost(`/account/emailVerification/${uuid}`)
			.reply(200, userSignedIn);
		const actual: UserSignedIn = await service.verifyEmail(uuid);
		expect(actual).toStrictEqual(userSignedIn);
	});

	it('resend the verification e-mail', async (): Promise<void> => {
		expect.assertions(1);
		const request: EmailVerificationResendRequest = {
			baseUrl: 'http://iqaros.local/',
		};
		mockedAxios.onPost('/account/emailVerification/resend', request)
			.reply(200);
		await expect(service.resendVerificationEmail(request)).resolves.not.toThrow();
	});

	it('sign in the user', async (): Promise<void> => {
		expect.assertions(1);
		mockedAxios.onPost('/account/signIn', credentials)
			.reply(200, {
				...userSignedIn,
				email: 'admin@xn--rksmrgs-5wao1o.josefsson.org',
			});
		const actual: UserSignedIn = await service.signIn(credentials);
		expect(actual).toStrictEqual({
			...userSignedIn,
			email: 'admin@räksmörgås.josefsson.org',
		});
	});

	it('sign in the user with expiration', async (): Promise<void> => {
		expect.assertions(1);
		const credentialsWithExpiration: UserCredentials = {
			...credentials,
			expiration: UserSessionExpiration.Week,
		};
		mockedAxios.onPost('/account/signIn', credentialsWithExpiration)
			.reply(200, userSignedIn);
		const actual: UserSignedIn = await service.signIn(credentialsWithExpiration);
		expect(actual).toStrictEqual(userSignedIn);
	});

	it('refresh JWT token', async (): Promise<void> => {
		expect.assertions(1);
		mockedAxios.onPost('/account/refreshToken')
			.reply(200, userSignedIn);
		const actual: UserSignedIn = await service.refreshToken();
		expect(actual).toStrictEqual(userSignedIn);
	});

});
