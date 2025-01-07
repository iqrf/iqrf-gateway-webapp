/**
 * Copyright 2023-2025 MICRORISC s.r.o.
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

import {
	NetworkConnectionType,
	NetworkInterfaceType,
} from '../../src/types/Network';
import { IpNetworkUtils } from '../../src/utils';

describe('IpNetworkUtils', (): void => {

	it('convert connection type to interface type', (): void => {
		expect.assertions(5);
		expect(IpNetworkUtils.connectionTypeToInterfaceType(null)).toBeNull();
		expect(IpNetworkUtils.connectionTypeToInterfaceType(NetworkConnectionType.Ethernet)).toBe(NetworkInterfaceType.ETHERNET);
		expect(IpNetworkUtils.connectionTypeToInterfaceType(NetworkConnectionType.WiFi)).toBe(NetworkInterfaceType.WIFI);
		expect(IpNetworkUtils.connectionTypeToInterfaceType(NetworkConnectionType.GSM)).toBe(NetworkInterfaceType.GSM);
		expect(IpNetworkUtils.connectionTypeToInterfaceType(NetworkConnectionType.VLAN)).toBe(NetworkInterfaceType.VLAN);
	});

});
