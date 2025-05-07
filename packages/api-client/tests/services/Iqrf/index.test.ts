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
	DpaMacrosService,
	InterfacePortsService,
	IqrfServices,
	UpgradeService,
} from '../../../src/services/Iqrf';
import { mockedClient } from '../../mocks/axios';

describe('IqrfServices', (): void => {

	/**
	 * @var {IqrfServices} services IQRF services
	 */
	const services: IqrfServices = new IqrfServices(mockedClient);

	test('returns DPA macros service instance', (): void => {
		expect.assertions(1);
		expect(services.getDpaMacrosService())
			.toBeInstanceOf(DpaMacrosService);
	});

	test('returns IQRF interface ports service instance', (): void => {
		expect.assertions(1);
		expect(services.getInterfacePortsService())
			.toBeInstanceOf(InterfacePortsService);
	});

	test('returns IQRF upgrade service instance', (): void => {
		expect.assertions(1);
		expect(services.getUpgradeService())
			.toBeInstanceOf(UpgradeService);
	});

});
