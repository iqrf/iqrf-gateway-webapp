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

import {mockedAxios, mockedClient} from '../mocks/axios';

import {VersionService} from '../../services';
import type {VersionBase, VersionIqrfGatewayWebapp} from '../../types';

describe('VersionService', (): void => {

	/**
	 * @var {VersionService} service Version service
	 */
	const service: VersionService = new VersionService(mockedClient);

	afterEach((): void => {
		jest.clearAllMocks();
	});

	it('fetch IQRF Gateway Daemon version', async (): Promise<void> => {
		expect.assertions(3);
		const version: VersionBase = {
			version: 'v2.3.0',
		};
		mockedAxios.get.mockResolvedValue({data: version});
		const actual: VersionBase = await service.getDaemon();
		expect(actual).toStrictEqual(version);
		expect(mockedAxios.get).toHaveBeenCalledTimes(1);
		expect(mockedAxios.get).toHaveBeenCalledWith('/version/daemon');
	});

	it('fetch IQRF Gateway Webapp version', async (): Promise<void> => {
		expect.assertions(3);
		const version: VersionIqrfGatewayWebapp = {
			version: 'v2.1.0-alpha',
			commit: 'b5e6ab29b192bc027615b5d4a712eebcbbe35eb9',
			pipeline: '3160',
		};
		mockedAxios.get.mockResolvedValue({data: version});
		const actual: VersionIqrfGatewayWebapp = await service.getWebapp();
		expect(actual).toStrictEqual(version);
		expect(mockedAxios.get).toHaveBeenCalledTimes(1);
		expect(mockedAxios.get).toHaveBeenCalledWith('/version/webapp');
	});

});
