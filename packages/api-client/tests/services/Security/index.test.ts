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
	ApiKeyService,
	CertificateService,
	SecurityServices,
	SshKeyService,
	UserService,
} from '../../../src/services/Security';
import { mockedClient } from '../../mocks/axios';

describe('SecurityServices', (): void => {

	/**
	 * @var {SecurityServices} services Security services
	 */
	const services: SecurityServices = new SecurityServices(mockedClient);

	it('returns API key service instance', (): void => {
		expect.assertions(1);
		expect(services.getApiKeyService())
			.toBeInstanceOf(ApiKeyService);
	});

	it('returns TLS certificate service instance', (): void => {
		expect.assertions(1);
		expect(services.getCertificateService())
			.toBeInstanceOf(CertificateService);
	});

	it('returns SSH key service instance', (): void => {
		expect.assertions(1);
		expect(services.getSshKeyService())
			.toBeInstanceOf(SshKeyService);
	});

	it('returns user management service instance', (): void => {
		expect.assertions(1);
		expect(services.getUserService())
			.toBeInstanceOf(UserService);
	});

});
