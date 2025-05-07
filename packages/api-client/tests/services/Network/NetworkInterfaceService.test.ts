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

import { beforeEach, describe, expect } from 'vitest';

import {
	NetworkInterfaceService,
} from '../../../src/services/Network';
import {
	type NetworkInterface,
	NetworkInterfaceState,
	NetworkInterfaceType,
} from '../../../src/types/Network';
import { mockedAxios, mockedClient } from '../../mocks/axios';

describe('NetworkInterfaceService', (): void => {

	/**
	 * @var {NetworkInterfaceService} service Network interface service
	 */
	const service: NetworkInterfaceService = new NetworkInterfaceService(mockedClient);

	/**
	 * @var {NetworkInterface[]} interfaces List of network interfaces
	 */
	const interfaces: NetworkInterface[] = [
		{
			name: 'br0',
			type: NetworkInterfaceType.BRIDGE,
			state: NetworkInterfaceState.Connected,
			connection: '',
			macAddress: '',
			manufacturer: null,
			model: null,
		},
		{
			name: 'eth0',
			type: NetworkInterfaceType.ETHERNET,
			state: NetworkInterfaceState.Connected,
			connection: 'f61b25c9-66d7-400e-add0-d2a30c57b65c',
			macAddress: '00:00:00:00:00:00',
			manufacturer: 'Manufacturer',
			model: 'Model',
		},
	];

	beforeEach((): void => {
		mockedAxios.reset();
	});

	test('fetch list of all network interfaces', async (): Promise<void> => {
		expect.assertions(1);
		mockedAxios.onGet('/network/interfaces')
			.reply(200, interfaces);
		const actual: NetworkInterface[] = await service.list();
		expect(actual).toStrictEqual(interfaces);
	});

	test('fetch list of Ethernet network interfaces', async (): Promise<void> => {
		expect.assertions(1);
		const ethernetInterfaces: NetworkInterface[] = [interfaces[1]];
		mockedAxios.onGet('/network/interfaces', {
			params: {
				type: NetworkInterfaceType.ETHERNET,
			},
		})
			.reply(200, ethernetInterfaces);
		const actual: NetworkInterface[] = await service.list(NetworkInterfaceType.ETHERNET);
		expect(actual).toStrictEqual(ethernetInterfaces);
	});

});
