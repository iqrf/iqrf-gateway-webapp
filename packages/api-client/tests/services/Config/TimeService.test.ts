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

import { describe, expect, it } from 'vitest';
import { mockedAxios, mockedClient } from '../../mocks/axios';

import { TimeService } from '../../../src/services/Gateway';
import type { TimeConfig, TimeSet, Timezone } from '../../../src/types/Gateway';

describe('TimeService', (): void => {

	/**
	 * @var {TimeService} service Time service
	 */
	const service: TimeService = new TimeService(mockedClient);

	it('fetch Time and NTP config', async(): Promise<void> => {
		expect.assertions(1);
		const config: TimeConfig = {
			abbrevation: 'CEST',
			formattedTime: '2023-10-26 13:06:11',
			formattedZone: '(UTC+0200) Europe/Prague (CEST)',
			gmtOffset: '+0200',
			gmtOffsetSec: 7200,
			ntpSync: true,
			ntpServers: [],
			localTimestamp: 1698325571,
			utcTimestamp: 1698318371,
			zoneName :'Europe/Prague',
		};
		mockedAxios.onGet('/gateway/time')
			.reply(200, config);
		await service.getTime()
			.then((actual: TimeConfig): void => {
				expect(actual).toStrictEqual(config);
			});
	});

	it('set Time and NTP config', async(): Promise<void> => {
		expect.assertions(0);
		const config: TimeSet = {
			ntpSync: true,
			ntpServers: [],
		};
		mockedAxios.onPost('/gateway/time', config)
			.reply(200);
		await service.setTime(config);
	});

	it('fetch available time zones', async(): Promise<void> => {
		expect.assertions(1);
		const timezones: Timezone[] = [
			{
				name: 'Europe/Prague',
				code: 'CEST',
				offset: '+0200',
			},
		];
		mockedAxios.onGet('/gateway/time/timezones')
			.reply(200, timezones);
		await service.getTimezones()
			.then((actual: Timezone[]): void => {
				expect(actual).toStrictEqual(timezones);
			});
	});

});
