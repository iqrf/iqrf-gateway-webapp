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

import { describe, expect } from 'vitest';

import {
	GatewayServices,
	HostnameService,
	InfoService,
	LogService,
	PowerService,
	TimeService,
} from '../../../src/services/Gateway';
import { mockedClient } from '../../mocks/axios';

describe('GatewayServices', (): void => {

	/**
	 * @var {GatewayServices} services IQRF gateway services
	 */
	const services: GatewayServices = new GatewayServices(mockedClient);

	test('returns hostname service instance', (): void => {
		expect.assertions(1);
		expect(services.getHostnameService())
			.toBeInstanceOf(HostnameService);
	});

	test('returns gateway information service instance', (): void => {
		expect.assertions(1);
		expect(services.getInfoService())
			.toBeInstanceOf(InfoService);
	});

	test('returns gateway log service instance', (): void => {
		expect.assertions(1);
		expect(services.getLogService())
			.toBeInstanceOf(LogService);
	});

	test('returns gateway power service instance', (): void => {
		expect.assertions(1);
		expect(services.getPowerService())
			.toBeInstanceOf(PowerService);
	});

	test('returns gateway time service instance', (): void => {
		expect.assertions(1);
		expect(services.getTimeService())
			.toBeInstanceOf(TimeService);
	});

});
