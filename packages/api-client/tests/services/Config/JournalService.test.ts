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

import { JournalService } from '../../../src/services/Config';
import {
	type JournalConfig,
	JournalPersistence,
	JournalTimeUnit,
} from '../../../src/types/Config';
import { mockedAxios, mockedClient } from '../../mocks/axios';

describe('JournalService', (): void => {

	beforeEach((): void => {
		mockedAxios.reset();
	});

	/**
	 * @var {JournalService} service Journal configuration service
	 */
	const service: JournalService = new JournalService(mockedClient);

	/**
	 * @var {JournalConfig} config Journal configuration
	 */
	const config: JournalConfig = {
		forwardToSyslog: false,
		persistence: JournalPersistence.Persistent,
		maxDiskSize: 20,
		maxFiles: 100,
		sizeRotation: { maxFileSize: 0 },
		timeRotation: { unit: JournalTimeUnit.Months, count: 1 },
	};

	test('get config', async (): Promise<void> => {
		expect.assertions(1);
		mockedAxios.onGet('/config/journal')
			.reply(200, config);
		const result = await service.getConfig();
		expect(result).toEqual(config);
	});

	test('update config', async (): Promise<void> => {
		expect.assertions(1);
		mockedAxios.onPut('/config/journal', config)
			.reply(200);
		await expect(service.updateConfig(config)).resolves.not.toThrowError();
	});

});
