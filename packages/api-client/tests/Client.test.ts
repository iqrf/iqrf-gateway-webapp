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

import axios, { type AxiosInstance, type AxiosRequestConfig } from 'axios';
import { beforeEach, describe, expect } from 'vitest';

import { Client } from '../src';
import {
	AccountService,
	FeatureService,
	InstallationService,
	OpenApiService,
	ServiceService,
	VersionService,
} from '../src/services';
import { CloudServices } from '../src/services/Cloud';
import { ConfigServices } from '../src/services/Config';
import { GatewayServices } from '../src/services/Gateway';
import { IqrfServices } from '../src/services/Iqrf';
import { MaintenanceServices } from '../src/services/Maintenance';
import { NetworkServices } from '../src/services/Network';
import { SecurityServices } from '../src/services/Security';

describe('Client', (): void => {

	/**
	 * @var {Client} client Client instance
	 */
	let client: Client;

	beforeEach((): void => {
		client = new Client();
	});

	test('can be instantiated', (): void => {
		expect.assertions(4);
		expect(client.getAxiosInstance())
			.toBeDefined();
		expect(client.getAxiosInstance().defaults.auth)
			.toBeUndefined();
		expect(client.getAxiosInstance().defaults.baseURL)
			.toBe('/api/');
		expect(client.getAxiosInstance().defaults.timeout)
			.toBe(30_000);
	});

	test('can be instantiated with custom Axios instance', (): void => {
		expect.assertions(4);
		const config: AxiosRequestConfig = {
			baseURL: 'https://iqrf-gw.exaple.com/api/',
			timeout: 5_000,
		};
		const axiosInstance: AxiosInstance = axios.create(config);
		client = new Client({ axiosInstance: axiosInstance });
		expect(client.getAxiosInstance())
			.toBeDefined();
		expect(client.getAxiosInstance().defaults.auth)
			.toBeUndefined();
		expect(client.getAxiosInstance().defaults.baseURL)
			.toBe('https://iqrf-gw.exaple.com/api/');
		expect(client.getAxiosInstance().defaults.timeout)
			.toBe(5_000);
	});

	test('can be instantiated with custom Axios instance configuration', (): void => {
		expect.assertions(4);
		const config: AxiosRequestConfig = {
			baseURL: 'https://iqrf-gw.exaple.com/api/',
			timeout: 5_000,
		};
		client = new Client({ config: config });
		expect(client.getAxiosInstance())
			.toBeDefined();
		expect(client.getAxiosInstance().defaults.auth)
			.toBeUndefined();
		expect(client.getAxiosInstance().defaults.baseURL)
			.toBe('https://iqrf-gw.exaple.com/api/');
		expect(client.getAxiosInstance().defaults.timeout)
			.toBe(5_000);
	});

	test('cannot be instantiated with custom Axios instance and custom Axios instance configuration', (): void => {
		expect.assertions(1);
		const config: AxiosRequestConfig = {
			baseURL: 'https://iqrf-gw.exaple.com/api/',
			timeout: 5_000,
		};
		const axiosInstance: AxiosInstance = axios.create(config);
		expect(() => new Client({ axiosInstance: axiosInstance, config: config }))
			.toThrow('Cannot instantiate Client with both axiosInstance and config.');
	});

	test('returns CloudServices instance', (): void => {
		expect.assertions(1);
		expect(client.getCloudServices()).toBeInstanceOf(CloudServices);
	});

	test('returns ConfigServices instance', (): void => {
		expect.assertions(1);
		expect(client.getConfigServices()).toBeInstanceOf(ConfigServices);
	});

	test('returns GatewayServices instance', (): void => {
		expect.assertions(1);
		expect(client.getGatewayServices()).toBeInstanceOf(GatewayServices);
	});

	test('returns IqrfServices instance', (): void => {
		expect.assertions(1);
		expect(client.getIqrfServices()).toBeInstanceOf(IqrfServices);
	});

	test('returns MaintenanceServices instance', (): void => {
		expect.assertions(1);
		expect(client.getMaintenanceServices()).toBeInstanceOf(MaintenanceServices);
	});

	test('returns NetworkServices instance', (): void => {
		expect.assertions(1);
		expect(client.getNetworkServices()).toBeInstanceOf(NetworkServices);
	});

	test('returns AccountService instance', (): void => {
		expect.assertions(1);
		expect(client.getAccountService()).toBeInstanceOf(AccountService);
	});

	test('returns FeatureService instance', (): void => {
		expect.assertions(1);
		expect(client.getFeatureService()).toBeInstanceOf(FeatureService);
	});

	test('returns InstallationService instance', (): void => {
		expect.assertions(1);
		expect(client.getInstallationService()).toBeInstanceOf(InstallationService);
	});

	test('returns OpenApiService instance', (): void => {
		expect.assertions(1);
		expect(client.getOpenApiService()).toBeInstanceOf(OpenApiService);
	});

	test('returns SecurityServices instance', (): void => {
		expect.assertions(1);
		expect(client.getSecurityServices()).toBeInstanceOf(SecurityServices);
	});

	test('returns ServiceService instance', (): void => {
		expect.assertions(1);
		expect(client.getServiceService()).toBeInstanceOf(ServiceService);
	});

	test('returns VersionService instance', (): void => {
		expect.assertions(1);
		expect(client.getVersionService()).toBeInstanceOf(VersionService);
	});

});
