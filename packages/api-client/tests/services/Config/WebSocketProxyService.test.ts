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

import { WebSocketProxyService } from '../../../src/services/Config';
import { type WebSocketProxyConfig } from '../../../src/types/Config';
import { mockedAxios, mockedClient } from '../../mocks/axios';

describe('WebSocketProxyService', (): void => {

	beforeEach((): void => {
		mockedAxios.reset();
	});

	/**
	 * @var {WebSocketProxyService} service WebSocket proxy server service
	 */
	const service: WebSocketProxyService = new WebSocketProxyService(mockedClient);

	/**
	 * @var {WebSocketProxyConfig} config WebSocket proxy service configuration
	 */
	const config: WebSocketProxyConfig = {
		host: 'localhost',
		port: 9_005,
		address: '127.0.0.1',
		upstream: 'ws://iqube.local/ws',
		token: 'iqrfgd2;1;ETi3v8RGLVGXb/uNenhskEiSH/2KussEbantcvjfGQ4=',
	};

	test('get config', async (): Promise<void> => {
		expect.assertions(1);
		mockedAxios.onGet('/config/ws-proxy')
			.reply(200, config);
		const result = await service.getConfig();
		expect(result).toEqual(config);
	});

	test('update config', async (): Promise<void> => {
		expect.assertions(1);
		mockedAxios.onPut('/config/ws-proxy', config)
			.reply(200);
		await expect(service.updateConfig(config)).resolves.not.toThrow();
	});

});
