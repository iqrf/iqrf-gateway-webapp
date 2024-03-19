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
	GatewayServices,
	HostnameService,
	InfoService,
	LogService,
	PowerService,
	SshKeyService,
	TimeService,
} from '../../src/services/Gateway';
import { mockedClient } from '../mocks/axios';

describe('GatewayServices', (): void => {

	/**
	 * @var {GatewayServices} services IQRF gateway services
	 */
	const services: GatewayServices = new GatewayServices(mockedClient);

	it('returns hostname service instance', (): void => {
		expect.assertions(1);
		expect(services.getHostnameService())
			.toBeInstanceOf(HostnameService);
	});

	it('returns gateway information service instance', (): void => {
		expect.assertions(1);
		expect(services.getInfoService())
			.toBeInstanceOf(InfoService);
	});

	it('returns gateway log service instance', (): void => {
		expect.assertions(1);
		expect(services.getLogService())
			.toBeInstanceOf(LogService);
	});

	it('returns gateway power service instance', (): void => {
		expect.assertions(1);
		expect(services.getPowerService())
			.toBeInstanceOf(PowerService);
	});

	it('returns gateway SSK key service instance', (): void => {
		expect.assertions(1);
		expect(services.getSshKeyService())
			.toBeInstanceOf(SshKeyService);
	});

	it('returns gateway time service instance', (): void => {
		expect.assertions(1);
		expect(services.getTimeService())
			.toBeInstanceOf(TimeService);
	});

});
