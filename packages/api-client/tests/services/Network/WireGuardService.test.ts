/**
 * Copyright 2023-2026 MICRORISC s.r.o.
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
	WireGuardIpStack,
	type WireGuardKeyPair,
	type WireGuardPeer,
	type WireGuardTunnelConfig,
	type WireGuardTunnelListEntry,
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

	/**
	 * @var {WireGuardTunnelConfig} tunnel WireGuard tunnel configuration
	 */
	const tunnel: WireGuardTunnelConfig = {
		id: tunnelId,
		name: 'wg0',
		privateKey: '+DsMmGWks1DawE1yc4UlOI6pbH3XVxdVArj9lLOyD18=',
		publicKey: '2OGuuIWQLKvtVYGntdHSoRb8MtDyKq6wxKd9vQphzDQ=',
		port: 51820,
		ipv4: {
			address: '10.0.0.1',
			prefix: 24,
		},
		ipv6: {
			address: '2001:db8::1',
			prefix: 64,
		},
		stack: WireGuardIpStack.DUAL,
	};

	/**
	 * @var {WireGuardTunnelListEntry[]} tunnels List of WireGuard tunnels
	 */
	const tunnels: WireGuardTunnelListEntry[] = [
		{
			active: false,
			enabled: true,
			id: tunnelId,
			name: 'wg0',
			stack: WireGuardIpStack.DUAL,
		},
	];

	/**
	 * @var {WireGuardPeer} peer WireGuard peer configuration
	 */
	const peer: WireGuardPeer = {
		id: peerId,
		publicKey: '2OGuuIWQLKvtVYGntdHSoRb8MtDyKq6wxKd9vQphzDQ=',
		psk: 'gw0IFEGK5ypiGglXBh2lAT1O2g0VSCuqd+7yNdW6Kew=',
		keepalive: 25,
		endpoint: 'vpn.example.org',
		port: 51280,
		allowedIPs: {
			ipv4: [
				{
					address: '10.0.0.2',
					prefix: 32,
				},
			],
			ipv6: [
				{
					address: '2001:db8::2',
					prefix: 128,
				},
			],
		},
		tunnelId,
	};

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

	test('fetch the list of WireGuard tunnels', async (): Promise<void> => {
		expect.assertions(1);
		mockedAxios.onGet('/network/wireguard').reply(200, tunnels);
		const actual: WireGuardTunnelListEntry[] = await service.listTunnels();
		expect(actual).toStrictEqual(tunnels);
	});

	test('create a new WireGuard tunnel', async (): Promise<void> => {
		expect.assertions(1);
		const request: WireGuardTunnelConfig = {
			id: tunnelId,
			name: 'wg0',
			privateKey: tunnel.privateKey,
			port: tunnel.port,
			ipv4: tunnel.ipv4,
			ipv6: tunnel.ipv6,
		};
		mockedAxios.onPost('/network/wireguard', request).reply(200, tunnel);
		const actual: WireGuardTunnelConfig = await service.createTunnel({ ...tunnel });
		expect(actual).toStrictEqual(tunnel);
	});

	test('update a WireGuard tunnel', async (): Promise<void> => {
		expect.assertions(1);
		const request: WireGuardTunnelConfig = {
			id: tunnelId,
			name: 'wg0',
			privateKey: tunnel.privateKey,
			port: tunnel.port,
			ipv4: tunnel.ipv4,
			ipv6: tunnel.ipv6,
		};
		mockedAxios.onPut('/network/wireguard/1', request).reply(200, tunnel);
		const actual: WireGuardTunnelConfig = await service.updateTunnel(tunnelId, { ...tunnel });
		expect(actual).toStrictEqual(tunnel);
	});

	test('deleteTunnel', async (): Promise<void> => {
		expect.assertions(0);
		mockedAxios.onDelete('/network/wireguard/1').reply(200);
		await service.deleteTunnel(tunnelId);
	});

	test('fetch a WireGuard tunnel', async (): Promise<void> => {
		expect.assertions(1);
		const response: WireGuardTunnelConfig = {
			id: tunnelId,
			name: 'wg0',
			port: tunnel.port,
			ipv4: tunnel.ipv4,
		};
		const expected: WireGuardTunnelConfig = {
			...response,
			stack: WireGuardIpStack.IPV4,
		};
		mockedAxios.onGet('/network/wireguard/1').reply(200, response);
		const actual: WireGuardTunnelConfig = await service.getTunnel(tunnelId);
		expect(actual).toStrictEqual(expected);
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

	test('create a new WireGuard peer', async (): Promise<void> => {
		expect.assertions(1);
		mockedAxios.onPost('/network/wireguard/peers', peer).reply(200, peer);
		const actual: WireGuardPeer = await service.createPeer({ ...peer });
		expect(actual).toStrictEqual(peer);
	});

	test('deletePeer', async (): Promise<void> => {
		expect.assertions(0);
		mockedAxios.onDelete('/network/wireguard/peers/1').reply(200);
		await service.deletePeer(peerId);
	});

	test('update a WireGuard peer', async (): Promise<void> => {
		expect.assertions(1);
		mockedAxios.onPut('/network/wireguard/peers/1', peer).reply(200, peer);
		const actual: WireGuardPeer = await service.updatePeer(peerId, { ...peer });
		expect(actual).toStrictEqual(peer);
	});

	test('fetch a WireGuard peer', async (): Promise<void> => {
		expect.assertions(1);
		mockedAxios.onGet('/network/wireguard/peers/1').reply(200, peer);
		const actual: WireGuardPeer = await service.getPeer(peerId);
		expect(actual).toStrictEqual(peer);
	});

	test('fetch WireGuard peers for tunnel', async (): Promise<void> => {
		expect.assertions(1);
		const peers: WireGuardPeer[] = [peer];
		mockedAxios.onGet('/network/wireguard/1/peers').reply(200, peers);
		const actual: WireGuardPeer[] = await service.getTunnelPeers(tunnelId);
		expect(actual).toStrictEqual(peers);
	});

	test('fetch all WireGuard peers', async (): Promise<void> => {
		expect.assertions(1);
		const peers: WireGuardPeer[] = [peer];
		mockedAxios.onGet('/network/wireguard/peers').reply(200, peers);
		const actual: WireGuardPeer[] = await service.getAllPeers();
		expect(actual).toStrictEqual(peers);
	});

});
