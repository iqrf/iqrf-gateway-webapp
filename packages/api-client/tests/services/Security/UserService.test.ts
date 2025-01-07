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

import { beforeEach, describe, expect, it } from 'vitest';

import { UserService } from '../../../src/services/Security';
import {
	AccountState,
	type EmailSentResponse,
	type UserInfo,
	UserLanguage,
	UserRole,
} from '../../../src/types';
import { mockedAxios, mockedClient } from '../../mocks/axios';

describe('UserService', (): void => {

	/**
	 * @var {UserService} service User service
	 */
	const service: UserService = new UserService(mockedClient);

	/**
	 * @var {object[]} rawUsers List of raw users
	 */
	const rawUsers = [
		{
			'id': 2,
			'username': 'roman',
			'email': 'roman@xn--ondrek-sta66a.eu',
			'role': 'admin',
			'language': 'en',
			'state': 'unverified',
		},
	];

	/**
	 * @var {UserInfo[]} users List of users
	 */
	const users: UserInfo[] = [
		{
			'id': 2,
			'username': 'roman',
			'email': 'roman@ondráček.eu',
			'role': UserRole.Admin,
			'language': UserLanguage.English,
			'state': AccountState.Unverified,
		},
	];

	beforeEach((): void => {
		mockedAxios.reset();
	});

	it('fetch the list of users', async (): Promise<void> => {
		expect.assertions(1);
		mockedAxios.onGet('/users')
			.reply(200, rawUsers);
		const actual: UserInfo[] = await service.list();
		expect(actual).toStrictEqual(users);
	});

	it('create a new user', async (): Promise<void> => {
		expect.assertions(1);
		const response: EmailSentResponse = {
			emailSent: false,
		};
		mockedAxios.onPost('/users', {
			username: 'roman',
			email: null,
			password: 'password',
			language: 'en',
			role: 'admin',
		})
			.reply(200, response);
		const actual: EmailSentResponse = await service.create({
			username: 'roman',
			email: null,
			password: 'password',
			language: UserLanguage.English,
			role: UserRole.Admin,
		});
		expect(actual).toStrictEqual(response);
	});

	it('fetch the user with ID `2`', async (): Promise<void> => {
		expect.assertions(1);
		mockedAxios.onGet('/users/2')
			.reply(200, rawUsers[0]);
		const actual: UserInfo = await service.get(2);
		expect(actual).toStrictEqual(users[0]);
	});

	it('update the user with ID `2`', async (): Promise<void> => {
		expect.assertions(1);
		const response: EmailSentResponse = {
			emailSent: true,
		};
		mockedAxios.onPut('/users/2', {
			username: 'roman',
			email: 'roman@xn--ondrek-sta66a.eu',
			language: 'en',
			role: 'admin',
		})
			.reply(200, response);
		const actual: EmailSentResponse = await service.update(2, {
			username: 'roman',
			email: 'roman@ondráček.eu',
			language: UserLanguage.English,
			role: UserRole.Admin,
		});
		expect(actual).toStrictEqual(response);
	});

	it('delete the user with ID `2`', async (): Promise<void> => {
		expect.assertions(0);
		mockedAxios.onDelete('/users/2')
			.reply(200);
		await service.delete(2);
	});

	it('resend the verification email to the user with ID `2`', async (): Promise<void> => {
		expect.assertions(0);
		mockedAxios.onPost('/users/2/resendVerification')
			.reply(200);
		await service.resendVerificationEmail(2);
	});

});
