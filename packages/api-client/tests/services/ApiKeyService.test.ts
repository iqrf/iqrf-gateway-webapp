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
import {describe, expect, it} from 'vitest';

import {DateTime} from 'luxon';

import {mockedAxios, mockedClient} from '../mocks/axios';

import {ApiKeyService} from '../../src/services';
import type {ApiKeyConfig, ApiKeyCreated, ApiKeyInfo} from '../../src/types';

describe('ApiKeyService', (): void => {

	/**
	 * @var {ApiKeyService} service API key service
	 */
	const service: ApiKeyService = new ApiKeyService(mockedClient);

	/**
	 * @var {ApiKeyInfo[]} rawApiKeys Raw API key
	 */
	const rawApiKeys = [
		{
			'id': 1,
			'description': 'Test',
			'expiration': '2023-07-13T12:00:00.000+02:00',
		}, {
			'id': 2,
			'description': 'Test #2',
			'expiration': null,
		},
	];

	/**
	 * @var {ApiKeyInfo[]} apiKeys API key
	 */
	const apiKeys: ApiKeyInfo[] = [
		{
			id: 1,
			description: 'Test',
			expiration: DateTime.fromISO('2023-07-13T12:00:00.000+02:00'),
		}, {
			id: 2,
			description: 'Test #2',
			expiration: null,
		},
	];

	it('fetch list of API keys', async (): Promise<void> => {
		expect.assertions(1);
		mockedAxios.onGet('/apiKeys')
			.reply(200, rawApiKeys);
		await service.list()
			.then((actual: ApiKeyInfo[]): void => {
				expect(actual).toStrictEqual(apiKeys);
			});
	});

	it('create API key', async (): Promise<void> => {
		expect.assertions(1);
		const key = 'ZJqlYCuPMSqGTRGuzpkLOu.nqkbk8VzEGpRgdpBvZkXxR8oZmQQ8C8J/Au61+L+JIQ=';
		const expiration: DateTime = DateTime.fromISO('2023-07-13T12:00:00+02:00');
		mockedAxios.onPost('/apiKeys', {
			description: 'Test',
			expiration: expiration.toISO(),
		})
			.reply(201, {...rawApiKeys[0], key});
		const config: ApiKeyConfig = {
			description: 'Test',
			expiration: expiration,
		};
		await service.create(config)
			.then((actual: ApiKeyCreated): void => {
				const expected: ApiKeyCreated = {
					...apiKeys[0],
					key: key,
				};
				expect(actual).toStrictEqual(expected);
			});
	});

	it('fetch API key with ID 1', async (): Promise<void> => {
		expect.assertions(1);
		mockedAxios.onGet('/apiKeys/1')
			.reply(200, rawApiKeys[0]);
		await service.fetch(1)
			.then((actual: ApiKeyInfo): void => {
				expect(actual).toStrictEqual(apiKeys[0]);
			});
	});

	it('update API key with ID 1', async (): Promise<void> => {
		expect.assertions(0);
		const config: ApiKeyConfig = {
			description: 'Test',
			expiration: null,
		};
		mockedAxios.onPut('/apiKeys/1', config)
			.reply(200);
		await service.edit(1, config);
	});

	it('delete API key with ID 1', async (): Promise<void> => {
		expect.assertions(0);
		mockedAxios.onDelete('/apiKeys/1')
			.reply(200);
		await service.delete(1);
	});

});
