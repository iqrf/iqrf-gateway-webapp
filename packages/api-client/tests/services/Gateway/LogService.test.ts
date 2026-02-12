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

import { beforeEach, describe, expect, test } from 'vitest';

import { LogService } from '../../../src/services/Gateway';
import { mockedAxios, mockedClient } from '../../mocks/axios';

describe('LogService', (): void => {

	/**
	 * @var {LogService} service Log service
	 */
	const service: LogService = new LogService(mockedClient);

	beforeEach((): void => {
		mockedAxios.reset();
	});

	test('list available logs', async (): Promise<void> => {
		expect.assertions(1);
		const response: string[] = [
			'iqrf-gateway-controller',
			'iqrf-gateway-daemon',
			'iqrf-gateway-setter',
			'iqrf-gateway-uploader',
		];
		mockedAxios.onGet('/gateway/logs')
			.reply(200, response);
		const actual: string[] = await service.listAvailable();
		expect(actual).toStrictEqual(response);
	});

});
