/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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

import { beforeEach, describe, expect, test, vi } from 'vitest';

import UrlBuilder from '../../helpers/urlBuilder';

describe('URL builder', (): void => {

	/**
	 * @var {URL} locationDevelopment Frontend location in development mode
	 */
	const locationDevelopment: URL = new URL('http://iqube.local:8081/gateway');

	/**
	 * @var {URL} locationProduction Frontend location in production mode
	 */
	const locationProduction: URL = new URL('https://iqube.local/gateway');

	/**
	 * Sets frontend mode
	 * @param {'production'|'development'} mode Frontend mode
	 */
	const setMode = (mode: 'production'|'development'): void => {
		const location = mode === 'development' ? locationDevelopment : locationProduction;
		vi.stubEnv('MODE', mode);
		vi.stubGlobal('location', location);

	};

	/**
	 * Restore all mocks before each test
	 */
	beforeEach((): void => {
		vi.restoreAllMocks();
		vi.unstubAllEnvs();
		vi.unstubAllGlobals();
	});

	test('get hostname', (): void => {
		expect.assertions(1);
		setMode('development');
		const builder: UrlBuilder = new UrlBuilder();
		expect(builder.getHostname()).toStrictEqual('iqube.local');
	});

	test('get port', (): void => {
		expect.assertions(1);
		setMode('development');
		const builder: UrlBuilder = new UrlBuilder();
		expect(builder.getPort()).toStrictEqual(':8081');
	});

	test('get base URL in development mode', (): void => {
		expect.assertions(1);
		vi.stubEnv('VITE_BASE_URL', '/system/');
		const url = 'http://iqube.local:8081/system/';
		setMode('development');
		const builder: UrlBuilder = new UrlBuilder();
		expect(builder.getBaseUrl()).toStrictEqual(url);
	});

	test('get REST API URL from environmental variable', (): void => {
		expect.assertions(1);
		const url = '//localhost:8080/v0';
		vi.stubEnv('VITE_URL_REST_API', url);
		const builder: UrlBuilder = new UrlBuilder();
		expect(builder.getRestApiUrl()).toStrictEqual(url);
	});

	test('get REST API URL in development mode', (): void => {
		expect.assertions(1);
		const url = '//iqube.local:8080/api/v0/';
		setMode('development');
		const builder: UrlBuilder = new UrlBuilder();
		expect(builder.getRestApiUrl()).toStrictEqual(url);
	});

	test('get REST API URL in production mode', (): void => {
		expect.assertions(1);
		const url = '//iqube.local/api/v0/';
		setMode('production');
		const builder: UrlBuilder = new UrlBuilder();
		expect(builder.getRestApiUrl()).toStrictEqual(url);
	});

	test('get REST API URL from hostname', (): void => {
		expect.assertions(2);
		setMode('development');
		const builder: UrlBuilder = new UrlBuilder();
		expect(builder.getRestApiUrlFromHostname('iqube.local'))
			.toStrictEqual('//iqube.local:8080/api/v0/');
		expect(builder.getRestApiUrlFromHostname('192.0.2.100'))
			.toStrictEqual('//192.0.2.100:8080/api/v0/');
	});

	test('get IQRF Gateway Daemon JSON API URL from environmental variable', (): void => {
		expect.assertions(1);
		const url = 'ws://iqube.local/ws';
		vi.stubEnv('VITE_URL_DAEMON_API', url);
		const builder: UrlBuilder = new UrlBuilder();
		expect(builder.getDaemonApiUrl()).toStrictEqual(url);
	});

	test('get IQRF Gateway Daemon JSON API URL in development mode', (): void => {
		expect.assertions(1);
		const url = 'ws://iqube.local:1338';
		setMode('development');
		const builder: UrlBuilder = new UrlBuilder();
		expect(builder.getDaemonApiUrl()).toStrictEqual(url);
	});

	test('get IQRF Gateway Daemon JSON API URL in production mode', (): void => {
		expect.assertions(1);
		const url = 'wss://iqube.local/ws';
		setMode('production');
		const builder: UrlBuilder = new UrlBuilder();
		expect(builder.getDaemonApiUrl()).toStrictEqual(url);
	});

	test('get IQRF Gateway Daemon monitor URL from environmental variable', (): void => {
		expect.assertions(1);
		const url = 'ws://iqube.local/wsMonitor';
		vi.stubEnv('VITE_URL_DAEMON_MONITOR', url);
		const builder: UrlBuilder = new UrlBuilder();
		expect(builder.getDaemonMonitorUrl()).toStrictEqual(url);
	});

	test('get IQRF Gateway Daemon monitor URL in development mode', (): void => {
		expect.assertions(1);
		const url = 'ws://iqube.local:1438';
		setMode('development');
		const builder: UrlBuilder = new UrlBuilder();
		expect(builder.getDaemonMonitorUrl()).toStrictEqual(url);
	});

	test('get IQRF Gateway Daemon monitor URL in production mode', (): void => {
		expect.assertions(1);
		const url = 'wss://iqube.local/wsMonitor';
		setMode('production');
		const builder: UrlBuilder = new UrlBuilder();
		expect(builder.getDaemonMonitorUrl()).toStrictEqual(url);
	});

	test('get IQRF network sync URL from environmental variable', (): void => {
		expect.assertions(1);
		const url = 'ws://iqube.local/sync';
		vi.stubEnv('VITE_URL_IQRF_SYNC', url);
		const builder: UrlBuilder = new UrlBuilder();
		expect(builder.getIqrfnetSyncUrl()).toStrictEqual(url);
	});

	test('get IQRF network sync URL in development mode', (): void => {
		expect.assertions(1);
		const url = 'ws://iqube.local:8881/sync';
		setMode('development');
		const builder: UrlBuilder = new UrlBuilder();
		expect(builder.getIqrfnetSyncUrl()).toStrictEqual(url);
	});

	test('get IQRF network sync URL in production mode', (): void => {
		expect.assertions(1);
		const url = 'wss://iqube.local/sync';
		setMode('production');
		const builder: UrlBuilder = new UrlBuilder();
		expect(builder.getIqrfnetSyncUrl()).toStrictEqual(url);
	});

});
