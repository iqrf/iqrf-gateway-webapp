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

import { beforeEach, describe, expect, it } from 'vitest';

import { HostnameService } from '../../../src/services/Gateway';
import { type Hostname } from '../../../src/types/Gateway/Hostname';
import { mockedAxios, mockedClient } from '../../mocks/axios';

describe('HostnameService', (): void => {

	/**
	 * @var {HostnameService} service Hostname service
	 */
	const service: HostnameService = new HostnameService(mockedClient);

	beforeEach((): void => {
		mockedAxios.reset();
	});

	it('set hostname', async(): Promise<void> => {
		expect.assertions(0);
		const hostname = 'testHostname';
		const data: Hostname = {
			hostname: hostname,
		};
		mockedAxios.onPost('/gateway/hostname', data)
			.reply(200);
		await service.setHostname(hostname);
	});

});
