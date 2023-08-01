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

import {DateTime} from 'luxon';

import {mockedAxios, mockedClient} from '../mocks/axios';

import {ApiKeyService} from '../../services';
import type {ApiKeyConfig, ApiKeyCreated, ApiKeyInfo} from '../../types';

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

	afterEach((): void => {
		jest.clearAllMocks();
	});

	it('fetch list of API keys', async (): Promise<void> => {
		expect.assertions(3);
		mockedAxios.get.mockResolvedValue({data: rawApiKeys});
		const actual: ApiKeyInfo[] = await service.list();
		const expected: ApiKeyInfo[] = apiKeys;
		expect(actual).toStrictEqual(expected);
		expect(mockedAxios.get).toHaveBeenCalledTimes(1);
		expect(mockedAxios.get).toHaveBeenCalledWith('/apiKeys');
	});

	it('create API key', async (): Promise<void> => {
		expect.assertions(3);
		const key = 'ZJqlYCuPMSqGTRGuzpkLOu.nqkbk8VzEGpRgdpBvZkXxR8oZmQQ8C8J/Au61+L+JIQ=';
		const expiration: DateTime = DateTime.fromISO('2023-07-13T12:00:00+02:00');
		mockedAxios.post.mockResolvedValue({data: {...rawApiKeys[0], key}});
		const config: ApiKeyConfig = {
			description: 'Test',
			expiration: expiration,
		};
		const actual: ApiKeyCreated = await service.create(config);
		const expected: ApiKeyCreated = {
			...apiKeys[0],
			key: key,
		};
		expect(actual).toStrictEqual(expected);
		expect(mockedAxios.post).toHaveBeenCalledTimes(1);
		expect(mockedAxios.post).toHaveBeenCalledWith('/apiKeys', {
			description: 'Test',
			expiration: expiration.toISO(),
		});
	});

	it('fetch API key with ID 1', async (): Promise<void> => {
		expect.assertions(3);
		mockedAxios.get.mockResolvedValue({data: rawApiKeys[0]});
		const actual: ApiKeyInfo = await service.fetch(1);
		expect(actual).toStrictEqual(apiKeys[0]);
		expect(mockedAxios.get).toHaveBeenCalledTimes(1);
		expect(mockedAxios.get).toHaveBeenCalledWith('/apiKeys/1');
	});

	it('update API key with ID 1', async (): Promise<void> => {
		expect.assertions(2);
		mockedAxios.put.mockResolvedValue({data: null});
		const config: ApiKeyConfig = {
			description: 'Test',
			expiration: null,
		};
		await service.edit(1, config);
		expect(mockedAxios.put).toHaveBeenCalledTimes(1);
		expect(mockedAxios.put).toHaveBeenCalledWith('/apiKeys/1', config);
	});

	it('delete API key with ID 1', async (): Promise<void> => {
		expect.assertions(2);
		mockedAxios.delete.mockResolvedValue({data: null});
		await service.delete(1);
		expect(mockedAxios.delete).toHaveBeenCalledTimes(1);
		expect(mockedAxios.delete).toHaveBeenCalledWith('/apiKeys/1');
	});

});
