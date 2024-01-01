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

import axios, {type AxiosInstance, type AxiosRequestConfig} from 'axios';
import {beforeEach, describe, expect, it} from 'vitest';

import {Client} from '../src';

import {version} from '../package.json';
import {
	AccountService,
	ApiKeyService,
	AuthenticationService,
	FeatureService,
	InstallationService,
	ServiceService,
	UserService,
	VersionService,
} from '../src/services';
import {CloudServices} from '../src/services/Cloud';
import {ConfigServices} from '../src/services/Config';
import {GatewayServices} from '../src/services/Gateway';
import {IqrfServices} from '../src/services/Iqrf';

describe('Client', (): void => {

	/**
	 * @var {Client} client Client instance
	 */
	let client: Client;

	/**
	 * @var {string} userAgent User agent
	 */
	const userAgent = `iqrf-gateway-webapp-js-client_v${version}`;

	beforeEach((): void => {
		client = new Client();
	});

	it('can be instantiated', (): void  => {
		expect.assertions(5);
		expect(client['axiosInstance'])
			.toBeDefined();
		expect(client['axiosInstance'].defaults.auth)
			.toBeUndefined();
		expect(client['axiosInstance'].defaults.baseURL)
			.toBe('/api/');
		expect(client['axiosInstance'].defaults.headers['User-Agent'])
			.toStrictEqual(userAgent);
		expect(client['axiosInstance'].defaults.timeout)
			.toBe(30_000);
	});

	it('can be instantiated with custom Axios instance', (): void  => {
		expect.assertions(5);
		const config: AxiosRequestConfig = {
			baseURL: 'https://iqrf-gw.exaple.com/api/',
			timeout: 5_000,
		};
		const axiosInstance: AxiosInstance = axios.create(config);
		client = new Client({axiosInstance: axiosInstance});
		expect(client['axiosInstance'])
			.toBeDefined();
		expect(client['axiosInstance'].defaults.auth)
			.toBeUndefined();
		expect(client['axiosInstance'].defaults.baseURL)
			.toBe('https://iqrf-gw.exaple.com/api/');
		expect(client['axiosInstance'].defaults.headers['User-Agent'])
			.toBe(userAgent);
		expect(client['axiosInstance'].defaults.timeout)
			.toBe(5_000);
	});

	it('can be instantiated with custom Axios instance configuration', (): void  => {
		expect.assertions(5);
		const config: AxiosRequestConfig = {
			baseURL: 'https://iqrf-gw.exaple.com/api/',
			timeout: 5_000,
		};
		client = new Client({config: config});
		expect(client['axiosInstance'])
			.toBeDefined();
		expect(client['axiosInstance'].defaults.auth)
			.toBeUndefined();
		expect(client['axiosInstance'].defaults.baseURL)
			.toBe('https://iqrf-gw.exaple.com/api/');
		expect(client['axiosInstance'].defaults.headers['User-Agent'])
			.toBe(userAgent);
		expect(client['axiosInstance'].defaults.timeout)
			.toBe(5_000);
	});

	it('cannot be instantiated with custom Axios instance and custom Axios instance configuration', (): void  => {
		expect.assertions(1);
		const config: AxiosRequestConfig = {
			baseURL: 'https://iqrf-gw.exaple.com/api/',
			timeout: 5_000,
		};
		const axiosInstance: AxiosInstance = axios.create(config);
		expect(() => (new Client({axiosInstance: axiosInstance, config: config})))
			.toThrow('Cannot instantiate Client with both axiosInstance and config.');
	});

	it('returns CloudServices instance', (): void  => {
		expect.assertions(1);
		expect(client.getCloudServices()).toBeInstanceOf(CloudServices);
	});

	it('returns ConfigServices instance', (): void  => {
		expect.assertions(1);
		expect(client.getConfigServices()).toBeInstanceOf(ConfigServices);
	});

	it('returns GatewayServices instance', (): void  => {
		expect.assertions(1);
		expect(client.getGatewayServices()).toBeInstanceOf(GatewayServices);
	});

	it('returns IqrfServices instance', (): void  => {
		expect.assertions(1);
		expect(client.getIqrfServices()).toBeInstanceOf(IqrfServices);
	});

	it('returns AccountService instance', (): void  => {
		expect.assertions(1);
		expect(client.getAccountService()).toBeInstanceOf(AccountService);
	});

	it('returns API key service instance', (): void  => {
		expect.assertions(1);
		expect(client.getApiKeyService()).toBeInstanceOf(ApiKeyService);
	});

	it('returns AuthenticationService instance', (): void  => {
		expect.assertions(1);
		expect(client.getAuthenticationService()).toBeInstanceOf(AuthenticationService);
	});

	it('returns FeatureService instance', (): void  => {
		expect.assertions(1);
		expect(client.getFeatureService()).toBeInstanceOf(FeatureService);
	});

	it('returns InstallationService instance', (): void  => {
		expect.assertions(1);
		expect(client.getInstallationService()).toBeInstanceOf(InstallationService);
	});

	it('returns ServiceService instance', (): void  => {
		expect.assertions(1);
		expect(client.getServiceService()).toBeInstanceOf(ServiceService);
	});

	it('returns UserService instance', (): void  => {
		expect.assertions(1);
		expect(client.getUserService()).toBeInstanceOf(UserService);
	});

	it('returns VersionService instance', (): void  => {
		expect.assertions(1);
		expect(client.getVersionService()).toBeInstanceOf(VersionService);
	});

});
