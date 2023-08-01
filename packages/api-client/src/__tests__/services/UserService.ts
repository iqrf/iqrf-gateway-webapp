/**
 * Copyright 2023 MICRORISC s.r.o.
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

import {mockedAxios, mockedClient} from '../mocks/axios';

import {UserService} from '../../services';
import {AccountState, type EmailSentResponse, type UserInfo, UserLanguage, UserRole} from '../../types';
import {expect} from '@jest/globals';

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

	afterEach((): void => {
		jest.clearAllMocks();
	});

	it('fetch the list of users', async (): Promise<void> => {
		expect.assertions(3);
		mockedAxios.get.mockResolvedValue({data: rawUsers});
		const actual: UserInfo[] = await service.list();
		expect(actual).toStrictEqual(users);
		expect(mockedAxios.get).toHaveBeenCalledTimes(1);
		expect(mockedAxios.get).toHaveBeenCalledWith('/users');
	});

	it('create a new user', async (): Promise<void> => {
		expect.assertions(3);
		const response: EmailSentResponse = {
			emailSent: false,
		};
		mockedAxios.post.mockResolvedValue({data: response});
		const actual: EmailSentResponse = await service.create({
			username: 'roman',
			email: null,
			password: 'password',
			language: UserLanguage.English,
			role: UserRole.Admin,
		});
		expect(actual).toStrictEqual(response);
		expect(mockedAxios.post).toHaveBeenCalledTimes(1);
		expect(mockedAxios.post).toHaveBeenCalledWith('/users', {
			username: 'roman',
			email: null,
			password: 'password',
			language: 'en',
			role: 'admin',
		});
	});

	it('fetch the user with ID `2`', async (): Promise<void> => {
		expect.assertions(3);
		mockedAxios.get.mockResolvedValue({data: rawUsers[0]});
		const actual: UserInfo = await service.fetch(2);
		expect(actual).toStrictEqual(users[0]);
		expect(mockedAxios.get).toHaveBeenCalledTimes(1);
		expect(mockedAxios.get).toHaveBeenCalledWith('/users/2');
	});

	it('update the user with ID `2`', async (): Promise<void> => {

		expect.assertions(3);
		const response: EmailSentResponse = {
			emailSent: true,
		};
		mockedAxios.put.mockResolvedValue({data: response});
		const actual: EmailSentResponse = await service.edit(2, {
			username: 'roman',
			email: 'roman@ondráček.eu',
			language: UserLanguage.English,
			role: UserRole.Admin,
		});
		expect(actual).toStrictEqual(response);
		expect(mockedAxios.put).toHaveBeenCalledTimes(1);
		expect(mockedAxios.put).toHaveBeenCalledWith('/users/2', {
			username: 'roman',
			email: 'roman@xn--ondrek-sta66a.eu',
			language: 'en',
			role: 'admin',
		});
	});

	it('delete the user with ID `2`', async (): Promise<void> => {
		expect.assertions(2);
		mockedAxios.delete.mockResolvedValue({data: rawUsers[0]});
		await service.delete(2);
		expect(mockedAxios.delete).toHaveBeenCalledTimes(1);
		expect(mockedAxios.delete).toHaveBeenCalledWith('/users/2');
	});

	it('resend the verification email to the user with ID `2`', async (): Promise<void> => {
		expect.assertions(2);
		mockedAxios.post.mockResolvedValue({});
		await service.resendVerificationEmail(2);
		expect(mockedAxios.post).toHaveBeenCalledTimes(1);
		expect(mockedAxios.post).toHaveBeenCalledWith('/users/2/resendVerification');
	});

});
