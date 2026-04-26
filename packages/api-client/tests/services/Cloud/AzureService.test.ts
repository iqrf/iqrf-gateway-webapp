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

import { AzureService } from '../../../src/services/Cloud';
import {
	type AzureIotHubConfig,
} from '../../../src/types/Cloud';
import { mockedAxios, mockedClient } from '../../mocks/axios';

describe('AzureService', (): void => {
	beforeEach((): void => {
		mockedAxios.reset();
	});

	/**
	 * @var {AzureService} service Microsoft Azure IoT Hub service
	 */
	const service: AzureService = new AzureService(mockedClient);

	/**
	 * @var {AzureIotHubConfig} config Microsoft Azure IoT Hub  configuration
	 */
	const config: AzureIotHubConfig = {
		connectionString:
			'HostName=iqrf.azure-devices.net;DeviceId=IQRFGW;SharedAccessKey=1234567890abcdefghijklmnopqrstuvwxyzABCDEFG=',
	};

	test('create MQTT connection', async (): Promise<void> => {
		expect.assertions(1);
		mockedAxios.onPost('/clouds/azure', config)
			.reply(200);
		await expect(
			service.createMqttInstance(config),
		).resolves.not.toThrowError();
	});

});
