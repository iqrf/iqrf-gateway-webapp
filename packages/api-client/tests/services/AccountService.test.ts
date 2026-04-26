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
	type UserAndRoleDetail,
	type UserCredentials,
	type UserInfo,
	type UserPasswordChange,
	type UserPasswordReset,
	type UserPasswordSet,
	type UserPreferences,
	type UserSignedIn,
	UserThemePreference,
	UserTimeFormatPreference,
} from '../../src/types';
import { type RoleInfo } from '../../src/types/Security';
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
		roleId: 0,
		language: Language.English,
		state: AccountState.Verified,
	};

	/**
	 * @var {RoleInfo} roleInfo Role information
	 */
	const roleInfo: RoleInfo = {
		id: 1,
		name: 'admin',
		description: 'Gateway administrator role',
		scopes: [],
		system: true,
		systemKey: 'admin',
	};

	/**
	 * @var {UserSignedIn} userSignedIn User signed in
	 */
	const userSignedIn: UserSignedIn = {
		user: userInfo,
		role: roleInfo,
		token: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJFUzM4NCJ9.eyJpYXQiOjE3MTA4Nzc3NjAsIm5iZiI6MTcxMDg3Nzc2MCwiZXhwIjoxNzEwODgzMTYwLCJ1aWQiOjEsImlzcyI6Ikxlbm92by1CNTEtODAiLCJqdGkiOiJMZW5vdm8tQjUxLTgwIn0._EguTP1nPp9N56tB40TrtXnuqKPZ3wlXERmvxiDtkHBzthJpQcwU7GkKgsIwL4f4I0LEPrmykZDmHlUSYG-BZiNPtGtFaiw_T5pC4FDYzUVLgitWg2rdKdKa5I7lmGuN',
	};

	/**
	 * @var {UserAndRoleDetail} UserAndRoleDetail User and role data response
	 */
	const userAndRoleDetail: UserAndRoleDetail = {
		user: userInfo,
		role: roleInfo,
	};

	beforeEach((): void => {
		mockedAxios.reset();
	});

	test('fetch information about the logged-in user', async (): Promise<void> => {
		expect.assertions(1);
		mockedAxios.onGet('/account')
			.reply(200, userAndRoleDetail);
		const actual: UserAndRoleDetail = await service.getInfo();
		expect(actual).toStrictEqual(userAndRoleDetail);
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
		await expect(service.updatePassword(request)).resolves.not.toThrow();
	});

	test('confirm password recovery - invalid UUID format', async (): Promise<void> => {
		expect.assertions(1);
		await expect(service.confirmPasswordRecovery('invalid-uuid', passwordResetRequest)).rejects
			.toThrow(new Error('Invalid password recovery request UUID.'));
	});

	test('confirm password recovery - invalid UUID version', async (): Promise<void> => {
		expect.assertions(1);
		await expect(service.confirmPasswordRecovery('60045219-7cbf-321e-a762-c90382cd8723', passwordResetRequest)).rejects
			.toThrow(new Error('Invalid password recovery request UUID version.'));
	});

	test('confirm password recovery', async (): Promise<void> => {
		expect.assertions(1);
		const uuid = '95b7edac-f3de-4dab-9cef-35a509b88f57';
		mockedAxios.onPost(`/account/password/recovery/${uuid}`, passwordResetRequest)
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
		mockedAxios.onPost('/account/password/recovery', request)
			.reply(200);
		await expect(service.requestPasswordRecovery(request)).resolves.not.toThrow();
	});

	test('set password for invited user', async (): Promise<void> => {
		expect.assertions(1);
		const uuid = '95b7edac-f3de-4dab-9cef-35a509b88f57';
		const requestBody: UserPasswordSet = {
			password: '8Yz#t>pL^sD|Uq&bW@',
		};
		mockedAxios.onPost(`/account/password/set/${uuid}`, requestBody)
			.reply(200, userSignedIn);
		const actual: UserSignedIn = await service.setPassword(uuid, requestBody);
		expect(actual).toStrictEqual(userSignedIn);
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
		await expect(service.updatePreferences(preferences)).resolves.not.toThrow();
	});

	test('verify e-mail address - invalid UUID format', async (): Promise<void> => {
		expect.assertions(1);
		await expect(service.verifyEmail('invalid-uuid')).rejects
			.toThrow(new Error('Invalid e-mail verification UUID.'));
	});

	test('verify e-mail address - invalid UUID version', async (): Promise<void> => {
		expect.assertions(1);
		await expect(service.verifyEmail('60045219-7cbf-321e-a762-c90382cd8723')).rejects
			.toThrow(new Error('Invalid e-mail verification UUID version.'));
	});

	test('verify e-mail address', async (): Promise<void> => {
		expect.assertions(1);
		const uuid = '95b7edac-f3de-4dab-9cef-35a509b88f57';
		mockedAxios.onGet(`/account/verification/${uuid}`)
			.reply(200, userSignedIn);
		const actual: UserSignedIn = await service.verifyEmail(uuid);
		expect(actual).toStrictEqual(userSignedIn);
	});

	test('resend the verification e-mail', async (): Promise<void> => {
		expect.assertions(1);
		const request: EmailVerificationResendRequest = {
			baseUrl: 'http://iqaros.local/',
		};
		mockedAxios.onPost('/account/verification/resend', request)
			.reply(200);
		await expect(service.resendVerificationEmail(request)).resolves.not.toThrow();
	});

	test('sign in the user', async (): Promise<void> => {
		expect.assertions(1);

		const mockData = { ...userSignedIn };
		mockData.user.email = 'admin@xn--rksmrgs-5wao1o.josefsson.org';

		const expectedData = { ...userSignedIn };
		expectedData.user.email = 'admin@räksmörgås.josefsson.org';

		mockedAxios.onPost('/account/signIn', credentials).reply(200, mockData);
		const actual: UserSignedIn = await service.signIn(credentials);
		expect(actual).toStrictEqual(expectedData);
	});

	test('refresh JWT token', async (): Promise<void> => {
		expect.assertions(1);
		mockedAxios.onPost('/account/tokenRefresh')
			.reply(200, userSignedIn);
		const actual: UserSignedIn = await service.refreshToken();
		expect(actual).toStrictEqual(userSignedIn);
	});

});
