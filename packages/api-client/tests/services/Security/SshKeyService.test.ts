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

import { DateTime } from 'luxon';
import { beforeEach, describe, expect, test } from 'vitest';

import { SshKeyService } from '../../../src/services/Security';
import {
	type SshKeyCreate,
	type SshKeyCreated,
	type SshKeyInfo,
	type SshKeyInfoRaw,
} from '../../../src/types/Security';
import { mockedAxios, mockedClient } from '../../mocks/axios';

describe('SshKeyService', (): void => {

	/**
	 * @var {SshKeyService} service SshKey service
	 */
	const service: SshKeyService = new SshKeyService(mockedClient);

	/**
	 * @var {SshKeyInfoRaw[]} sshKeysRaw SSH key info list response raw data
	 */
	const sshKeysRaw: SshKeyInfoRaw[] = [
		{
			id: 1,
			type: 'ssh-rsa',
			key: 'ssh-rsa AAAAB3NzaC1yc2EAAAABIwAAAQEAklOUpkDHrfHY17SbrmTIpNLTGK9Tjom/BWDSU' +
				'GPl+nafzlHDTYW7hdI4yZ5ew18JH4JW9jbhUFrviQzM7xlELEVf4h9lFX5QVkbPppSwg0cda3' +
				'Pbv7kOdJ/MTyBlWXFCR+HAo3FXRitBqxiX1nKhXpHAZsMciLq8V6RjsNAQwdsdMFvSlVK/7XA' +
				't3FaoJoAsncM1Q9x5+3V0Ww68/eIFmb1zuUFljQJKprrX88XypNDvjYNby6vw/Pb0rwert/En' +
				'mZ+AW4OZPnTPI89ZPmVMLuayrD2cE86Z/il8b+gw3r3+1nKatmIkjn2so1d01QraTlMqVSsbx' +
				'NrRFi9wrf+M7Q== schacon@mylaptop.local',
			description: 'schacon@mylaptop.local',
			hash: 'SHA256:pyIviSnX1wCz//lp7kkixlk/1GJNUafzrCwBGMqe3ZI',
			createdAt: '2026-01-01T00:00:00.000Z',
		},
	];

	/**
	 * @var {SshKeyInfo[]} sshKeys SSH key info list response data
	 */
	const sshKeys: SshKeyInfo[] = [
		{
			...sshKeysRaw[0],
			createdAt: DateTime.fromISO('2026-01-01T00:00:00.000Z'),
		},
	];

	beforeEach((): void => {
		mockedAxios.reset();
	});

	test('list supported SSH key types', async (): Promise<void> => {
		expect.assertions(1);
		const response: string[] = [
			'ssh-ed25519',
			'ssh-ed25519-cert-v01@openssh.com',
			'sk-ssh-ed25519@openssh.com',
			'sk-ssh-ed25519-cert-v01@openssh.com',
			'ecdsa-sha2-nistp256',
			'ecdsa-sha2-nistp256-cert-v01@openssh.com',
			'ecdsa-sha2-nistp384',
			'ecdsa-sha2-nistp384-cert-v01@openssh.com',
			'ecdsa-sha2-nistp521',
			'ecdsa-sha2-nistp521-cert-v01@openssh.com',
			'sk-ecdsa-sha2-nistp256@openssh.com',
			'sk-ecdsa-sha2-nistp256-cert-v01@openssh.com',
			'ssh-dss',
			'ssh-dss-cert-v01@openssh.com',
			'ssh-rsa',
			'ssh-rsa-cert-v01@openssh.com',
		];
		mockedAxios.onGet('/security/sshKeys/types')
			.reply(200, response);
		const actual: string[] = await service.listKeyTypes();
		expect(actual).toStrictEqual(response);
	});

	test('list SSH keys', async (): Promise<void> => {
		expect.assertions(1);
		mockedAxios.onGet('/security/sshKeys')
			.reply(200, sshKeysRaw);
		const actual: SshKeyInfo[] = await service.list();
		expect(actual).toStrictEqual(sshKeys);
	});

	test('get SSH key', async (): Promise<void> => {
		expect.assertions(1);
		const id: number = 1;
		mockedAxios.onGet(`/security/sshKeys/${id.toString()}`)
			.reply(200, sshKeysRaw[0]);
		const actual: SshKeyInfo = await service.getKey(id);
		expect(actual).toStrictEqual(sshKeys[0]);
	});

	test('delete SSH key', async (): Promise<void> => {
		expect.assertions(0);
		const id: number = 1;
		mockedAxios.onDelete(`/security/sshKeys/${id.toString()}`)
			.reply(204);
		await service.deleteKey(id);
	});

	test('create SSH keys', async (): Promise<void> => {
		expect.assertions(1);
		const request: SshKeyCreate[] = [
			{
				key: 'ssh-rsa AAAAB3NzaC1yc2EAAAABIwAAAQEAklOUpkDHrfHY17SbrmTIpNLTGK9Tjom/BWDSU\n' +
					'GPl+nafzlHDTYW7hdI4yZ5ew18JH4JW9jbhUFrviQzM7xlELEVf4h9lFX5QVkbPppSwg0cda3\n' +
					'Pbv7kOdJ/MTyBlWXFCR+HAo3FXRitBqxiX1nKhXpHAZsMciLq8V6RjsNAQwdsdMFvSlVK/7XA\n' +
					't3FaoJoAsncM1Q9x5+3V0Ww68/eIFmb1zuUFljQJKprrX88XypNDvjYNby6vw/Pb0rwert/En\n' +
					'mZ+AW4OZPnTPI89ZPmVMLuayrD2cE86Z/il8b+gw3r3+1nKatmIkjn2so1d01QraTlMqVSsbx\n' +
					'NrRFi9wrf+M7Q== schacon@mylaptop.local',
				description: 'schacon@mylaptop.local',
			},
		];
		const response: SshKeyCreated = {
			failedKeys: [],
		};
		mockedAxios.onPost('/security/sshKeys', request)
			.reply(200, response);
		const actual: SshKeyCreated = await service.createSshKeys(request);
		expect(actual).toStrictEqual(response);
	});

});
