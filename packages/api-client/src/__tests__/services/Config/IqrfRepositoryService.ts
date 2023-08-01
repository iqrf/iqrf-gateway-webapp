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

import {mockedAxios, mockedClient} from '../../mocks/axios';

import {IqrfRepositoryService} from '../../../services';
import type {IqrfRepositoryConfig} from '../../../types';

describe('IqrfRepositoryService', (): void => {

	/**
	 * @var {IqrfRepositoryService} service IQRF Repository service
	 */
	const service: IqrfRepositoryService = new IqrfRepositoryService(mockedClient);

	afterEach((): void => {
		jest.clearAllMocks();
	});

	it('fetch IQRF Repository config', async (): Promise<void> => {
		expect.assertions(3);
		const config: IqrfRepositoryConfig = {
			'apiEndpoint': 'https://repository.iqrfalliance.org/api/',
			'credentials': {
				'username': 'username',
				'password': 'password',
			},
		};
		mockedAxios.get.mockResolvedValue({data: config});
		const actual: IqrfRepositoryConfig = await service.getConfig();
		expect(actual).toStrictEqual(config);
		expect(mockedAxios.get).toHaveBeenCalledTimes(1);
		expect(mockedAxios.get).toHaveBeenCalledWith('/config/iqrf-repository');
	});

	it('update IQRF Repository config', async (): Promise<void> => {
		expect.assertions(2);
		const config: IqrfRepositoryConfig = {
			'apiEndpoint': 'https://devrepo.iqrfalliance.org/api/',
			'credentials': {
				'username': null,
				'password': null,
			},
		};
		mockedAxios.put.mockResolvedValue({
			data: config,
		});
		await service.editConfig(config);
		expect(mockedAxios.put).toHaveBeenCalledTimes(1);
		expect(mockedAxios.put).toHaveBeenCalledWith('/config/iqrf-repository', config);
	});

});
