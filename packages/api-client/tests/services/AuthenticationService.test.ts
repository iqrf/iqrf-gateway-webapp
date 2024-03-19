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

import { AuthenticationService } from '../../src/services';
import {
	AccountState,
	type UserCredentials,
	UserLanguage,
	UserRole,
	UserSessionExpiration,
	type UserSignedIn,
} from '../../src/types';
import { mockedAxios, mockedClient } from '../mocks/axios';

describe('AuthenticationService', (): void => {

	/**
	 * @var {AuthenticationService} service Authentication service
	 */
	const service: AuthenticationService = new AuthenticationService(mockedClient);

	/**
	 * @var {UserCredentials} credentials User credentials
	 */
	const credentials: UserCredentials = {
		username: 'admin',
		password: 'iqrf',
	};

	/**
	 * @var {UserSignedIn} userSignedIn User signed in
	 */
	const userSignedIn: UserSignedIn = {
		id: 1,
		username: 'admin',
		email: 'admin@example.com',
		role: UserRole.Admin,
		language: UserLanguage.English,
		state: AccountState.Verified,
		token: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJFUzM4NCJ9.eyJpYXQiOjE3MTA4Nzc3NjAsIm5iZiI6MTcxMDg3Nzc2MCwiZXhwIjoxNzEwODgzMTYwLCJ1aWQiOjEsImlzcyI6Ikxlbm92by1CNTEtODAiLCJqdGkiOiJMZW5vdm8tQjUxLTgwIn0._EguTP1nPp9N56tB40TrtXnuqKPZ3wlXERmvxiDtkHBzthJpQcwU7GkKgsIwL4f4I0LEPrmykZDmHlUSYG-BZiNPtGtFaiw_T5pC4FDYzUVLgitWg2rdKdKa5I7lmGuN',
	};

	beforeEach((): void => {
		mockedAxios.reset();
	});

	it('sign in the user', async (): Promise<void> => {
		expect.assertions(1);
		mockedAxios.onPost('/user/signIn', credentials)
			.reply(200, {
				...userSignedIn,
				email: 'admin@xn--rksmrgs-5wao1o.josefsson.org',
			});
		await service.signIn(credentials)
			.then((actual: UserSignedIn): void => {
				expect(actual).toStrictEqual({
					...userSignedIn,
					email: 'admin@räksmörgås.josefsson.org',
				});
			});
	});

	it('sign in the user with expiration', async (): Promise<void> => {
		expect.assertions(1);
		const credentialsWithExpiration: UserCredentials = {
			...credentials,
			expiration: UserSessionExpiration.Week,
		};
		mockedAxios.onPost('/user/signIn', credentialsWithExpiration)
			.reply(200, userSignedIn);
		await service.signIn(credentialsWithExpiration)
			.then((actual: UserSignedIn): void => {
				expect(actual).toStrictEqual(userSignedIn);
			});
	});

	it('refresh JWT token', async (): Promise<void> => {
		expect.assertions(1);
		mockedAxios.onPost('/user/refreshToken')
			.reply(200, userSignedIn);
		await service.refreshToken()
			.then((actual: UserSignedIn): void => {
				expect(actual).toStrictEqual(userSignedIn);
			});
	});

	it('verify the user - invalid UUID format', async (): Promise<void> => {
		expect.assertions(1);
		expect(() => service.verify('invalid-uuid'))
			.toThrow(new Error('Invalid verification UUID.'));
	});

	it('verify the user - invalid UUID version', async (): Promise<void> => {
		expect.assertions(1);
		expect(() => service.verify('60045219-7cbf-321e-a762-c90382cd8723'))
			.toThrow(new Error('Invalid verification UUID version.'));
	});

	it('verify the user', async (): Promise<void> => {
		expect.assertions(1);
		const uuid = '95b7edac-f3de-4dab-9cef-35a509b88f57';
		mockedAxios.onGet(`/user/verify/${uuid}`)
			.reply(200, userSignedIn);
		await service.verify(uuid)
			.then((actual: UserSignedIn): void => {
				expect(actual).toStrictEqual(userSignedIn);
			});
	});

});
