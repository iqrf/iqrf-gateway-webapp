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

import { beforeEach, describe, expect, test } from 'vitest';

import {
	WireGuardService,
} from '../../../src/services/Network';
import {
	type WireGuardKeyPair,
} from '../../../src/types/Network';
import { mockedAxios, mockedClient } from '../../mocks/axios';

describe('WireGuardService', (): void => {

	/**
	 * @var {WireGuardService} service Network interface service
	 */
	const service: WireGuardService = new WireGuardService(mockedClient);

	/**
	 * @var {number} tunnelId Tunnel ID
	 */
	const tunnelId: number = 1;

	/**
	 * @var {number} peerId Peer ID
	 */
	const peerId: number = 1;

	beforeEach((): void => {
		mockedAxios.reset();
	});

	test('generateKeyPair', async (): Promise<void> => {
		expect.assertions(1);
		const keyPair: WireGuardKeyPair = {
			privateKey: '+DsMmGWks1DawE1yc4UlOI6pbH3XVxdVArj9lLOyD18=',
			publicKey: '2OGuuIWQLKvtVYGntdHSoRb8MtDyKq6wxKd9vQphzDQ=',
		};
		mockedAxios.onPost('/network/wireguard/keypair').reply(200, keyPair);
		const actual: WireGuardKeyPair = await service.generateKeyPair();
		expect(actual).toStrictEqual(keyPair);
	});

	test('deleteTunnel', async (): Promise<void> => {
		expect.assertions(0);
		mockedAxios.onDelete('/network/wireguard/1').reply(200);
		await service.deleteTunnel(tunnelId);
	});

	test('activateTunnel', async (): Promise<void> => {
		expect.assertions(0);
		mockedAxios.onPost('/network/wireguard/1/activate').reply(200);
		await service.activateTunnel(tunnelId);
	});

	test('deactivateTunnel', async (): Promise<void> => {
		expect.assertions(0);
		mockedAxios.onPost('/network/wireguard/1/deactivate').reply(200);
		await service.deactivateTunnel(tunnelId);
	});

	test('enableTunnel', async (): Promise<void> => {
		expect.assertions(0);
		mockedAxios.onPost('/network/wireguard/1/enable').reply(200);
		await service.enableTunnel(tunnelId);
	});

	test('disableTunnel', async (): Promise<void> => {
		expect.assertions(0);
		mockedAxios.onPost('/network/wireguard/1/disable').reply(200);
		await service.disableTunnel(tunnelId);
	});

	test('deletePeer', async (): Promise<void> => {
		expect.assertions(0);
		mockedAxios.onDelete('/network/wireguard/peers/1').reply(200);
		await service.deletePeer(peerId);
	});

});
