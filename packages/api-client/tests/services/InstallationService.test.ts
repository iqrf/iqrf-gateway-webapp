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

import { beforeEach, describe, expect, test } from 'vitest';

import { InstallationService } from '../../src/services';
import { type InstallationChecks } from '../../src/types';
import { mockedAxios, mockedClient } from '../mocks/axios';

describe('InstallationService', (): void => {

	/**
	 * @var {InstallationService} service Installation service
	 */
	const service: InstallationService = new InstallationService(mockedClient);

	beforeEach((): void => {
		mockedAxios.reset();
	});

	test('check the installation', async (): Promise<void> => {
		expect.assertions(1);
		mockedAxios.onGet('/installation')
			.reply(200, {
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
	});

});
