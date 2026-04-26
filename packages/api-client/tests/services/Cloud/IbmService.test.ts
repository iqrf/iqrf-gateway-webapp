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

import { IbmService } from '../../../src/services/Cloud';
import {
	type IbmCloudConfig,
} from '../../../src/types/Cloud';
import { mockedAxios, mockedClient } from '../../mocks/axios';

describe('IbmService', (): void => {
	beforeEach((): void => {
		mockedAxios.reset();
	});

	/**
	 * @var {IbmService} service IBM Cloud service
	 */
	const service: IbmService = new IbmService(mockedClient);

	/**
	 * @var {IbmCloudConfig} config IBM Cloud configuration
	 */
	const config: IbmCloudConfig = {
		organizationId: '3j1ozv',
		deviceType: 'IQRF_GW2',
		deviceId: 'UP_IQRF_GW2',
		token: 'UP_IQRF_GW2_token',
		eventId: 'iqrf',
	};

	test('create MQTT connection', async (): Promise<void> => {
		expect.assertions(1);
		mockedAxios.onPost('/clouds/ibmCloud', config)
			.reply(200);
		await expect(
			service.createMqttInstance(config),
		).resolves.not.toThrowError();
	});
});
