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

import { describe, expect, test, vi } from 'vitest';

import { SshKeyUtils } from '../../src/utils';

describe('SshKeyUtils', (): void => {

	/**
	 * @var {string} key SSH public key
	 */
	const key = 'ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIJsFi4AP1s9eIJlEz3jutG+/Ez7jDoiGVSkN9DFlAk0W roman@Lenovo-Thinkpad-E595';

	/**
	 * @var {string[]} supportedKeyTypes Supported SSH public key types
	 */
	const supportedKeyTypes: string[] = ['ssh-rsa', 'ssh-ed25519'];

	test('validate supported and valid SSH public key', (): void => {
		expect.assertions(1);
		const validateSpy = vi.spyOn(SshKeyUtils, 'validatePublicKey');
		SshKeyUtils.validatePublicKey(key, supportedKeyTypes);
		expect(validateSpy).toHaveReturned();
	});

	test('validate supported and invalid SSH public key', (): void => {
		expect.assertions(1);
		expect((): void => {
			SshKeyUtils.validatePublicKey('ssh-rsa ', supportedKeyTypes);
		}).toThrow('Invalid SSH key - invalid number of parts');
	});

	test('validate unsupported and valid SSH public key', (): void => {
		expect.assertions(1);
		expect((): void => {
			SshKeyUtils.validatePublicKey(key, ['sha-rsa']);
		}).toThrow('Invalid SSH key - invalid key type');
	});

});
