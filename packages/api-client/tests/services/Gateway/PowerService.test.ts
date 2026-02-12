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

import { DateTime, Duration } from 'luxon';
import { beforeEach, describe, expect, test } from 'vitest';

import { PowerService } from '../../../src/services/Gateway';
import {
	type GatewayUptime, type GatewayUptimeRaw,
	type PowerActionResponse,
	type PowerActionResponseRaw,
} from '../../../src/types/Gateway';
import { mockedAxios, mockedClient } from '../../mocks/axios';

describe('PowerService', (): void => {

	/**
	 * @var {PowerActionResponse} powerActionResponse Power action response
	 */
	const powerActionResponse: PowerActionResponse = {
		timestamp: DateTime.fromISO('2025-06-15T15:06:40.000Z'),
	};

	/**
	 * @var {PowerActionResponseRaw} powerActionResponseRaw Power action response raw
	 */
	const powerActionResponseRaw: PowerActionResponseRaw = {
		timestamp: 1_750_000_000,
	};

	/**
	 * @var {PowerService} service Power service
	 */
	const service: PowerService = new PowerService(mockedClient);

	beforeEach((): void => {
		mockedAxios.reset();
	});

	test('power off the gateway', async (): Promise<void> => {
		expect.assertions(1);
		mockedAxios.onPost('/gateway/power/poweroff')
			.reply(200, powerActionResponseRaw);
		const actual: PowerActionResponse = await service.powerOff();
		expect(actual).toStrictEqual(powerActionResponse);
	});

	test('reboot the gateway', async (): Promise<void> => {
		expect.assertions(1);
		mockedAxios.onPost('/gateway/power/reboot')
			.reply(200, powerActionResponseRaw);
		const actual: PowerActionResponse = await service.reboot();
		expect(actual).toStrictEqual(powerActionResponse);
	});

	test('get uptime stats', async (): Promise<void> => {
		expect.assertions(1);
		const response: GatewayUptimeRaw[] = [
			{
				id: 1,
				start: '2025-02-09T19:49:27+00:00',
				shutdown: '2025-02-28T06:40:04+00:00',
				running: 1_594_237,
				sleeping: 0,
				downtime: 500,
				graceful: false,
				kernel: 'Linux-5.15.139-legacy-sunxi-armv7l-with-glibc2.36',
			},
		];
		mockedAxios.onGet('/gateway/power/stats')
			.reply(200, response);
		const actual: GatewayUptime[] = await service.getStats();
		const expected: GatewayUptime[] = [
			{
				...response[0],
				start: DateTime.fromISO('2025-02-09T19:49:27+00:00'),
				shutdown: DateTime.fromISO('2025-02-28T06:40:04+00:00'),
				sleeping: Duration.fromISO('PT0S'),
				running: Duration.fromISO('PT1594237S'),
				downtime: Duration.fromISO('PT500S'),
			},
		];
		expect(actual).toStrictEqual(expected);
	});

});
