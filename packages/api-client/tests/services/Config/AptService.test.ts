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

import { beforeEach, describe, expect, test } from 'vitest';

import { AptService } from '../../../src/services/Config';
import {
	type AptConfig,
	type AptConfigRaw,
} from '../../../src/types/Config';
import { mockedAxios, mockedClient } from '../../mocks/axios';

describe('AptService', (): void => {

	beforeEach((): void => {
		mockedAxios.reset();
	});

	/**
	 * @var {AptService} service APT package manager configuration service
	 */
	const service: AptService = new AptService(mockedClient);

	/**
	 * @var {AptConfig} config APT configuration
	 */
	const config: AptConfig = {
		enabled: false,
		packageListUpdateInterval: 1,
		packageUpdateInterval: 1,
		packageRemovalInterval: 21,
		rebootOnKernelUpdate: false,
	};

	/**
	 * @var {AptConfigRaw} rawConfig Raw APT configuration
	 */
	const rawConfig: AptConfigRaw = {
		'APT::Periodic::Enable': '0',
		'APT::Periodic::Update-Package-Lists': '1',
		'APT::Periodic::Unattended-Upgrade': '1',
		'APT::Periodic::AutocleanInterval': '21',
		'Unattended-Upgrade::Automatic-Reboot': 'false',
	};

	test('get config', async (): Promise<void> => {
		expect.assertions(1);
		mockedAxios.onGet('/config/apt')
			.reply(200, rawConfig);
		const result = await service.getConfig();
		expect(result).toEqual(config);
	});

	test('update config', async (): Promise<void> => {
		expect.assertions(1);
		mockedAxios.onPut('/config/apt', rawConfig)
			.reply(200);
		await expect(service.updateConfig(config)).resolves.not.toThrowError();
	});

	test('update service state', async (): Promise<void> => {
		expect.assertions(2);
		const parameters = [
			{ param: true, body: { 'APT::Periodic::Enable': '1' } },
			{ param: false, body: { 'APT::Periodic::Enable': '0' } },
		];
		for (const parameter of parameters) {
			mockedAxios.onPut('/config/apt', parameter.body)
				.reply(200);
			await expect(service.updateServiceState(parameter.param)).resolves.not.toThrowError();
		}
	});

});
