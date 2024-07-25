/**
 * Copyright 2023-2024 MICRORISC s.r.o.
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
	MobileOperatorService,
	ModemService,
	NetworkConnectionService,
	NetworkInterfaceService,
	NetworkServices,
	WireGuardService,
} from '../../../src/services/Network';
import { mockedClient } from '../../mocks/axios';

describe('NetworkServices', (): void => {

	/**
	 * @var {NetworkServices} services Network services
	 */
	const services: NetworkServices = new NetworkServices(mockedClient);

	it('returns MobileOperatorService instance', (): void => {
		expect.assertions(1);
		expect(services.getMobileOperatorService())
			.toBeInstanceOf(MobileOperatorService);
	});

	it('returns ModemService instance', (): void => {
		expect.assertions(1);
		expect(services.getModemService())
			.toBeInstanceOf(ModemService);
	});

	it('returns NetworkConnectionService instance', (): void => {
		expect.assertions(1);
		expect(services.getNetworkConnectionService())
			.toBeInstanceOf(NetworkConnectionService);
	});

	it('returns NetworkInterfaceService instance', (): void => {
		expect.assertions(1);
		expect(services.getNetworkInterfaceService())
			.toBeInstanceOf(NetworkInterfaceService);
	});

	it('returns WireGuardService instance', (): void => {
		expect.assertions(1);
		expect(services.getWireGuardService())
			.toBeInstanceOf(WireGuardService);
	});

});
