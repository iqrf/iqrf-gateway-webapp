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
import {describe, expect, it} from 'vitest';

import {mockedAxios, mockedClient} from '../../mocks/axios';

import {IqrfRepositoryService} from '@/services';
import type {IqrfRepositoryConfig} from '@/types';

describe('IqrfRepositoryService', (): void => {

	/**
	 * @var {IqrfRepositoryService} service IQRF Repository service
	 */
	const service: IqrfRepositoryService = new IqrfRepositoryService(mockedClient);

	it('fetch IQRF Repository config', async (): Promise<void> => {
		expect.assertions(1);
		const config: IqrfRepositoryConfig = {
			'apiEndpoint': 'https://repository.iqrfalliance.org/api/',
			'credentials': {
				'username': 'username',
				'password': 'password',
			},
		};
		mockedAxios.onGet('/config/iqrf-repository')
			.reply(200, config);
		await service.getConfig()
			.then((actual: IqrfRepositoryConfig): void => {
				expect(actual).toStrictEqual(config);
			});
	});

	it('update IQRF Repository config', async (): Promise<void> => {
		expect.assertions(0);
		const config: IqrfRepositoryConfig = {
			'apiEndpoint': 'https://devrepo.iqrfalliance.org/api/',
			'credentials': {
				'username': null,
				'password': null,
			},
		};
		mockedAxios.onPut('/config/iqrf-repository', config)
			.reply(200);
		await service.editConfig(config);
	});

});
