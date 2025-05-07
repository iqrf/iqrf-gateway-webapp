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
	BackupService,
	MaintenanceServices,
} from '../../../src/services/Maintenance';
import { mockedClient } from '../../mocks/axios';

describe('MaintenanceServices', (): void => {

	/**
	 * @var {MaintenanceServices} services Maintenance services
	 */
	const services: MaintenanceServices = new MaintenanceServices(mockedClient);

	test('returns backup service instance', (): void => {
		expect.assertions(1);
		expect(services.getBackupService())
			.toBeInstanceOf(BackupService);
	});

});
