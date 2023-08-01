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

import {InstallationService} from '../../services';
import type {InstallationChecks} from '../../types';

describe('InstallationService', (): void => {

	/**
	 * @var {InstallationService} service Installation service
	 */
	const service: InstallationService = new InstallationService(mockedClient);

	afterEach((): void => {
		jest.clearAllMocks();
	});

	it('check the installation', async (): Promise<void> => {
		expect.assertions(3);
		mockedAxios.get.mockResolvedValue({
			data: {
				'allMigrationsExecuted': true,
				'phpModules': {
					'allExtensionsLoaded': true,
				},
				'sudo': {
					'user': 'www-data',
					'exists': true,
					'userSudo': true,
				},
				'dependencies': [],
				'hasUsers': true,
			},
		});
		const actual: InstallationChecks = await service.check();
		expect(actual).toStrictEqual({
			allMigrationsExecuted: true,
			phpModules: {
				allExtensionsLoaded: true,
			},
			sudo: {
				user: 'www-data',
				exists: true,
				userSudo: true,
			},
			dependencies: [],
			hasUsers: true,
		});
		expect(mockedAxios.get).toHaveBeenCalledTimes(1);
		expect(mockedAxios.get).toHaveBeenCalledWith('/installation');
	});

});
