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

import { DateTime } from 'luxon';
import { beforeEach, describe, expect, test } from 'vitest';

import { DaemonApiTokenService } from '../../../src/services/Security';
import {
	type DaemonApiTokenCreate,
	type DaemonApiTokenCreated,
	DaemonApiTokenExpirationUnit,
	type DaemonApiTokenInfo,
	type DaemonApiTokenInfoRaw,
	DaemonApiTokenStatus,
} from '../../../src/types/Security';
import { mockedAxios, mockedClient } from '../../mocks/axios';

describe('DaemonApiTokenService', (): void => {

	/**
	 * @var {DaemonApiTokenService} service Daemon API token service
	 */
	const service: DaemonApiTokenService = new DaemonApiTokenService(mockedClient);

	/**
	 * @var {DaemonApiTokenInfoRaw[]} rawTokenInfo Raw token information
	 */
	const rawTokenInfo: DaemonApiTokenInfoRaw[] = [
		{
			id: 1,
			owner: 'test',
			created_at: '2026-02-20T12:00:00Z',
			expires_at: '2027-02-20T12:00:00Z',
			status: DaemonApiTokenStatus.Revoked,
			service: false,
			invalidated_at: '2027-05-20T12:00:00Z',
		},
		{
			id: 2,
			owner: 'test',
			created_at: '2026-05-20T12:00:00Z',
			expires_at: '2027-05-20T12:00:00Z',
			status: DaemonApiTokenStatus.Valid,
			service: false,
			invalidated_at: null,
		},
	];

	/**
	 * @var {DaemonApiTokenInfo[]} tokenInfo Token information
	 */
	const tokenInfo: DaemonApiTokenInfo[] = [
		{
			id: 1,
			owner: 'test',
			created_at: DateTime.fromISO('2026-02-20T12:00:00Z'),
			expires_at: DateTime.fromISO('2027-02-20T12:00:00Z'),
			status: DaemonApiTokenStatus.Revoked,
			service: false,
			invalidated_at: DateTime.fromISO('2027-05-20T12:00:00Z'),
		},
		{
			id: 2,
			owner: 'test',
			created_at: DateTime.fromISO('2026-05-20T12:00:00Z'),
			expires_at: DateTime.fromISO('2027-05-20T12:00:00Z'),
			status: DaemonApiTokenStatus.Valid,
			service: false,
			invalidated_at: null,
		},
	];

	/**
	 * @var {DaemonApiTokenCreated[]} tokens Tokens
	 */
	const tokens: DaemonApiTokenCreated[] = [
		{
			id: 1,
			token: 'iqrfgd2;1;zDrcvQaXWopzJ+DbfkpGq3Tn00wkt3n6fExj8iUsYio=',
		},
		{
			id: 2,
			token: 'iqrfgd2;2;uiJCz+dKPZIaa1bn8vq3xcmktblBsoyoWz0UBpU8Cx8=',
		},
	];

	beforeEach((): void => {
		mockedAxios.reset();
	});

	test('fetch list of Daemon API access tokens', async (): Promise<void> => {
		expect.assertions(1);
		mockedAxios.onGet('/security/daemon-access-tokens')
			.reply(200, rawTokenInfo);
		const actual: DaemonApiTokenInfo[] = await service.list();
		expect(actual).toStrictEqual(tokenInfo);
	});

	test('get Daemon API access token with ID 1', async (): Promise<void> => {
		expect.assertions(1);
		mockedAxios.onGet('/security/daemon-access-tokens/1')
			.reply(200, rawTokenInfo[0]);
		const actual: DaemonApiTokenInfo = await service.get(1);
		expect(actual).toStrictEqual(tokenInfo[0]);
	});

	test('create Daemon API access token with relative expiration', async (): Promise<void> => {
		expect.assertions(1);
		const data: DaemonApiTokenCreate = {
			owner: 'test',
			unit: DaemonApiTokenExpirationUnit.Year,
			count: 1,
		};
		mockedAxios.onPost('/security/daemon-access-tokens', data)
			.reply(201, { ...tokens[0] });
		const actual: DaemonApiTokenCreated = await service.create(data);
		expect(actual).toStrictEqual(tokens[0]);
	});

	test('create Daemon API access token with absolute expiration', async (): Promise<void> => {
		expect.assertions(1);
		const data: DaemonApiTokenCreate = {
			owner: 'test',
			expiration: '2027-02-20T12:00:00Z',
		};
		mockedAxios.onPost('/security/daemon-access-tokens', data)
			.reply(201, { ...tokens[0] });
		const actual: DaemonApiTokenCreated = await service.create(data);
		expect(actual).toStrictEqual(tokens[0]);
	});

	test('revoke Daemon API access token', async (): Promise<void> => {
		expect.assertions(0);
		mockedAxios.onPost('/security/daemon-access-tokens/1/revoke')
			.reply(200);
		await service.revoke(1);
	});

	test('rotate Daemon API access token', async (): Promise<void> => {
		expect.assertions(1);
		mockedAxios.onPost('/security/daemon-access-tokens/1/rotate')
			.reply(200, { ...tokens[1] });
		const actual: DaemonApiTokenCreated = await service.rotate(1);
		expect(actual).toStrictEqual(tokens[1]);
	});

});
