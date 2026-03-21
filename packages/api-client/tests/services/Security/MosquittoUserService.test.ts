/**
 * Copyright 2023-2026 MICRORISC s.r.o.
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

import { DateTime } from 'luxon';
import { beforeEach, describe, expect, test } from 'vitest';

import { MosquittoUserService } from '../../../src/services/Security/MosquittoUserService';
import {
	type MosquittoUser,
	type MosquittoUserCreate,
	type MosquittoUserRaw,
	MosquittoUserState,
} from '../../../src/types/Security';
import { mockedAxios, mockedClient } from '../../mocks/axios';

describe('MosquittoUserService', (): void => {

	/**
	 * @var {MosquittoUserService} service Mosquitto user service
	 */
	const service: MosquittoUserService = new MosquittoUserService(mockedClient);

	/**
	 * @var {MosquittoUserRaw[]} rawUsers Raw API response mosquitto users
	 */
	const rawUsers: MosquittoUserRaw[] = [
		{
			id: 1,
			username: 'test',
			createdAt: '2026-02-20T10:55:20.132Z',
			state: MosquittoUserState.Active,
			blockedAt: null,
		},
		{
			id: 2,
			username: 'testuser',
			createdAt: '2026-02-20T12:55:20.132+02:00',
			state: MosquittoUserState.Blocked,
			blockedAt: '2026-02-20T12:55:30.771+02:00',
		},
	];

	/**
	 * @var {MosquittoUser[]} users Parsed mosquitto users
	 */
	const users: MosquittoUser[] = [
		{
			id: 1,
			username: 'test',
			createdAt: DateTime.fromISO('2026-02-20T10:55:20.132Z'),
			state: MosquittoUserState.Active,
			blockedAt: null,
		},
		{
			id: 2,
			username: 'testuser',
			createdAt: DateTime.fromISO('2026-02-20T12:55:20.132+02:00'),
			state: MosquittoUserState.Blocked,
			blockedAt: DateTime.fromISO('2026-02-20T12:55:30.771+02:00'),
		},
	];

	beforeEach((): void => {
		mockedAxios.reset();
	});

	test('fetch list of mosquitto users', async (): Promise<void> => {
		expect.assertions(1);
		mockedAxios.onGet('/security/mosquitto-users')
			.reply(200, rawUsers);
		const actual: MosquittoUser[] = await service.list();
		expect(actual).toStrictEqual(users);
	});

	test('get mosquitto user by ID', async (): Promise<void> => {
		expect.assertions(1);
		mockedAxios.onGet('/security/mosquitto-users/1')
			.reply(200, rawUsers[0]);
		const actual: MosquittoUser = await service.get(1);
		expect(actual).toStrictEqual(users[0]);
	});

	test('create mosquitto user', async (): Promise<void> => {
		expect.assertions(1);
		const data: MosquittoUserCreate = {
			username: 'test',
			password: '8Yz#t>pL^sD|Uq&bW@',

		};
		mockedAxios.onPost('/security/mosquitto-users', data)
			.reply(201, { ...rawUsers[0] });
		const actual: MosquittoUser = await service.create(data);
		expect(actual).toStrictEqual(users[0]);
	});

	test('block mosquitto user', async (): Promise<void> => {
		expect.assertions(0);
		mockedAxios.onPost('/security/mosquitto-users/1/block')
			.reply(200);
		await service.block(1);
	});

});
