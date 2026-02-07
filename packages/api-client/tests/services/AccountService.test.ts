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

import { Language } from '@iqrf/iqrf-ui-common-types';
import { beforeEach, describe, expect, test } from 'vitest';

import { AccountService } from '../../src/services';
import {
	type AccountEdit,
	AccountState,
	type EmailSentResponse,
	type EmailVerificationResendRequest,
	type UserAccountRecovery,
	type UserCredentials,
	type UserInfo,
	type UserPasswordChange,
	type UserPasswordReset,
	type UserPreferences,
	UserRole,
	UserSessionExpiration,
	type UserSignedIn,
	UserThemePreference,
	UserTimeFormatPreference,
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
	 * @var {UserPreferences} preferences User preferences
	 */
	const preferences: UserPreferences = {
		theme: UserThemePreference.Auto,
		timeFormat: UserTimeFormatPreference.Auto,
	};

	/**
	 * @var {UserInfo} userInfo User information
	 */
	const userInfo: UserInfo = {
		id: 1,
		username: 'admin',
		email: 'admin@example.com',
		role: UserRole.Admin,
		language: Language.English,
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

	test('fetch information about the logged-in user', async (): Promise<void> => {
		expect.assertions(1);
		mockedAxios.onGet('/account')
			.reply(200, userInfo);
		const actual: UserInfo = await service.getInfo();
		expect(actual).toStrictEqual(userInfo);
	});

	test('edit the user', async (): Promise<void> => {
		expect.assertions(1);
		const request: AccountEdit = {
			username: 'admin',
			email: 'admin@example.org',
			language: Language.English,
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

	test('change user\' password', async (): Promise<void> => {
		expect.assertions(1);
		const request: UserPasswordChange = {
			old: 'iqrf',
			new: 'new-password',
			baseUrl: 'http://iqaros.local/',
		};
		mockedAxios.onPut('/account/password', request)
			.reply(200);
		await expect(service.updatePassword(request)).resolves.not.toThrowError();
	});

	test('confirm password recovery - invalid UUID format', async (): Promise<void> => {
		expect.assertions(1);
		await expect(service.confirmPasswordRecovery('invalid-uuid', passwordResetRequest)).rejects
			.toThrowError(new Error('Invalid password recovery request UUID.'));
	});

	test('confirm password recovery - invalid UUID version', async (): Promise<void> => {
		expect.assertions(1);
		await expect(service.confirmPasswordRecovery('60045219-7cbf-321e-a762-c90382cd8723', passwordResetRequest)).rejects
			.toThrowError(new Error('Invalid password recovery request UUID version.'));
	});

	test('confirm password recovery', async (): Promise<void> => {
		expect.assertions(1);
		const uuid = '95b7edac-f3de-4dab-9cef-35a509b88f57';
		mockedAxios.onPost(`/account/passwordRecovery/${uuid}`, passwordResetRequest)
			.reply(200, userSignedIn);
		const actual: UserSignedIn = await service.confirmPasswordRecovery(uuid, passwordResetRequest);
		expect(actual).toStrictEqual(userSignedIn);
	});

	test('request password recovery', async (): Promise<void> => {
		expect.assertions(1);
		const request: UserAccountRecovery = {
			username: 'admin',
			baseUrl: 'http://iqaros.local/',
		};
		mockedAxios.onPost('/account/passwordRecovery', request)
			.reply(200);
		await expect(service.requestPasswordRecovery(request)).resolves.not.toThrowError();
	});

	test('get user preferences', async (): Promise<void> => {
		expect.assertions(1);
		mockedAxios.onGet('/account/preferences')
			.reply(200, preferences);
		const actual: UserPreferences = await service.getPreferences();
		expect(actual).toStrictEqual(preferences);
	});

	test('update user preferences', async (): Promise<void> => {
		expect.assertions(1);
		mockedAxios.onPut('/account/preferences', preferences)
			.reply(200);
		await expect(service.updatePreferences(preferences)).resolves.not.toThrowError();
	});

	test('verify e-mail address - invalid UUID format', async (): Promise<void> => {
		expect.assertions(1);
		await expect(service.verifyEmail('invalid-uuid')).rejects
			.toThrowError(new Error('Invalid e-mail verification UUID.'));
	});

	test('verify e-mail address - invalid UUID version', async (): Promise<void> => {
		expect.assertions(1);
		await expect(service.verifyEmail('60045219-7cbf-321e-a762-c90382cd8723')).rejects
			.toThrowError(new Error('Invalid e-mail verification UUID version.'));
	});

	test('verify e-mail address', async (): Promise<void> => {
		expect.assertions(1);
		const uuid = '95b7edac-f3de-4dab-9cef-35a509b88f57';
		mockedAxios.onGet(`/account/emailVerification/${uuid}`)
			.reply(200, userSignedIn);
		const actual: UserSignedIn = await service.verifyEmail(uuid);
		expect(actual).toStrictEqual(userSignedIn);
	});

	test('resend the verification e-mail', async (): Promise<void> => {
		expect.assertions(1);
		const request: EmailVerificationResendRequest = {
			baseUrl: 'http://iqaros.local/',
		};
		mockedAxios.onPost('/account/emailVerification/resend', request)
			.reply(200);
		await expect(service.resendVerificationEmail(request)).resolves.not.toThrowError();
	});

	test('sign in the user', async (): Promise<void> => {
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

	test('sign in the user with expiration', async (): Promise<void> => {
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

	test('refresh JWT token', async (): Promise<void> => {
		expect.assertions(1);
		mockedAxios.onPost('/account/tokenRefresh')
			.reply(200, userSignedIn);
		const actual: UserSignedIn = await service.refreshToken();
		expect(actual).toStrictEqual(userSignedIn);
	});

});
