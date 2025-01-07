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
	AwsService,
	AzureService,
	CloudServices,
	IbmService,
} from '../../../src/services/Cloud';
import { mockedClient } from '../../mocks/axios';

describe('CloudServices', (): void => {

	/**
	 * @var {CloudServices} services Cloud services
	 */
	const services: CloudServices = new CloudServices(mockedClient);

	it('returns AWS IoT service instance', (): void => {
		expect.assertions(1);
		expect(services.getAwsService())
			.toBeInstanceOf(AwsService);
	});

	it('returns Azure IoT Hub service instance', (): void => {
		expect.assertions(1);
		expect(services.getAzureService())
			.toBeInstanceOf(AzureService);
	});

	it('returns IBM cloud service instance', (): void => {
		expect.assertions(1);
		expect(services.getIbmService())
			.toBeInstanceOf(IbmService);
	});

});
