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
import { beforeEach, describe, expect, it } from 'vitest';

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

	it('can be instantiated', (): void => {
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

	it('can be instantiated with custom Axios instance', (): void => {
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

	it('can be instantiated with custom Axios instance configuration', (): void => {
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

	it('cannot be instantiated with custom Axios instance and custom Axios instance configuration', (): void => {
		expect.assertions(1);
		const config: AxiosRequestConfig = {
			baseURL: 'https://iqrf-gw.exaple.com/api/',
			timeout: 5_000,
		};
		const axiosInstance: AxiosInstance = axios.create(config);
		expect(() => new Client({ axiosInstance: axiosInstance, config: config }))
			.toThrow('Cannot instantiate Client with both axiosInstance and config.');
	});

	it('returns CloudServices instance', (): void => {
		expect.assertions(1);
		expect(client.getCloudServices()).toBeInstanceOf(CloudServices);
	});

	it('returns ConfigServices instance', (): void => {
		expect.assertions(1);
		expect(client.getConfigServices()).toBeInstanceOf(ConfigServices);
	});

	it('returns GatewayServices instance', (): void => {
		expect.assertions(1);
		expect(client.getGatewayServices()).toBeInstanceOf(GatewayServices);
	});

	it('returns IqrfServices instance', (): void => {
		expect.assertions(1);
		expect(client.getIqrfServices()).toBeInstanceOf(IqrfServices);
	});

	it('returns MaintenanceServices instance', (): void => {
		expect.assertions(1);
		expect(client.getMaintenanceServices()).toBeInstanceOf(MaintenanceServices);
	});

	it('returns NetworkServices instance', (): void => {
		expect.assertions(1);
		expect(client.getNetworkServices()).toBeInstanceOf(NetworkServices);
	});

	it('returns AccountService instance', (): void => {
		expect.assertions(1);
		expect(client.getAccountService()).toBeInstanceOf(AccountService);
	});

	it('returns FeatureService instance', (): void => {
		expect.assertions(1);
		expect(client.getFeatureService()).toBeInstanceOf(FeatureService);
	});

	it('returns InstallationService instance', (): void => {
		expect.assertions(1);
		expect(client.getInstallationService()).toBeInstanceOf(InstallationService);
	});

	it('returns OpenApiService instance', (): void => {
		expect.assertions(1);
		expect(client.getOpenApiService()).toBeInstanceOf(OpenApiService);
	});

	it('returns SecurityServices instance', (): void => {
		expect.assertions(1);
		expect(client.getSecurityServices()).toBeInstanceOf(SecurityServices);
	});

	it('returns ServiceService instance', (): void => {
		expect.assertions(1);
		expect(client.getServiceService()).toBeInstanceOf(ServiceService);
	});

	it('returns VersionService instance', (): void => {
		expect.assertions(1);
		expect(client.getVersionService()).toBeInstanceOf(VersionService);
	});

});
