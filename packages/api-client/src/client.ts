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

import axios, {
	type AxiosInstance,
	type AxiosInterceptorOptions,
	type AxiosRequestConfig,
	type AxiosResponse,
	type InternalAxiosRequestConfig,
} from 'axios';

import {
	AccountService,
	FeatureService,
	InstallationService,
	OpenApiService,
	ServiceService,
	VersionService,
} from './services';
import { CloudServices } from './services/Cloud';
import { ConfigServices } from './services/Config';
import { GatewayServices } from './services/Gateway';
import { IqrfServices } from './services/Iqrf';
import { MaintenanceServices } from './services/Maintenance';
import { NetworkServices } from './services/Network';
import { SecurityServices } from './services/Security';

/**
 * IQRF Gateway Webapp API client options
 */
export interface ClientOptions {

	/**
	 * Axios instance
	 */
	axiosInstance?: AxiosInstance;

	/**
	 * Axios instance configuration
	 */
	config?: AxiosRequestConfig;

	/**
	 * API key or JWT token
	 */
	token?: string|null;

}

/**
 * # IQRF Gateway Webapp API client class
 *
 * Client is a wrapper around API methods of IQRF Gateway Webapp providing shared configuration for Axios instance.
 *
 * ## Instantiate with defaults
 * ```typescript
 * const client = new Client();
 * ```
 *
 * ## Instantiate with custom configuration
 * ```typescript
 * const config: AxiosRequestConfig = {
 *     baseURL: 'https://iqrf-gw.exaple.org/api/',
 * };
 * const client = new Client({ config });
 * ```
 *
 * ## Instantiate with custom Axios instance **advanced**
 * ```typescript
 * const config: AxiosRequestConfig = {
 *     baseURL: 'https://iqrf-gw.exaple.org/api/',
 * }
 * const axiosInstance = axios.create(config);
 * const client = new Client({ axiosInstance });
 */
export class Client {

	/**
	 * @property {AxiosInstance} axiosInstance Axios instance
	 * @private
	 */
	private readonly axiosInstance: AxiosInstance;

	/**
	 * @property {AxiosRequestConfig} defaultAxiosConfig Default Axios instance configuration
	 * @private
	 */
	private readonly defaultAxiosConfig: AxiosRequestConfig = {
		/** IQRF Gateway Webapp API base URL */
		baseURL: '/api/',
		/** Timeout in milliseconds */
		timeout: 30_000,
	};

	/**
	 * @property {string|null} token API key or JWT token
	 * @private
	 */
	private token: string|null = null;

	/**
	 * @property {number|undefined} requestInterceptorId Request interceptor ID
	 * @private
	 */
	private readonly requestInterceptorId: number|undefined;

	/**
	 * Constructs IQRF Gateway Webapp API client
	 * @param {{axiosInstance, config, token}} __namedParameters Client options
	 * @param {AxiosInstance} __namedParameters.axiosInstance Axios instance
	 * @param {AxiosRequestConfig} __namedParameters.config Axios instance configuration
	 * @param {string|null} __namedParameters.token API key or JWT token
	 */
	public constructor({ axiosInstance, config, token }: ClientOptions = {}) {
		if (axiosInstance && config) {
			throw new Error('Cannot instantiate Client with both axiosInstance and config.');
		}
		if (axiosInstance) {
			this.axiosInstance = axiosInstance;
		} else if (config) {
			config = {
				...this.defaultAxiosConfig,
				...config,
			};
			this.axiosInstance = axios.create(config);
		} else {
			this.axiosInstance = axios.create(this.defaultAxiosConfig);
		}
		this.token = token ?? null;
		this.registerRequestInterceptor();
	}

	/**
	 * Sets API key or JWT token
	 * @param {string|null} token API key or JWT token
	 * @todo Implement API key and JWT validation
	 */
	public setToken(token: string|null): void {
		this.token = token;
	}

	/**
	 * Returns Axios instance
	 * @return {AxiosInstance} Axios instance
	 */
	public getAxiosInstance(): AxiosInstance {
		return this.axiosInstance;
	}

	/**
	 * Returns Account service
	 * @return {AccountService} Account service
	 */
	public getAccountService(): AccountService {
		return new AccountService(this);
	}

	/**
	 * Returns Cloud services
	 * @return {CloudServices} Cloud services
	 */
	public getCloudServices(): CloudServices {
		return new CloudServices(this);
	}

	/**
	 * Returns configuration services
	 * @return {ConfigServices} Configuration services
	 */
	public getConfigServices(): ConfigServices {
		return new ConfigServices(this);
	}

	/**
	 * Returns Gateway services
	 * @return {GatewayServices} Gateway services
	 */
	public getGatewayServices(): GatewayServices {
		return new GatewayServices(this);
	}

	/**
	 * Returns IQRF services
	 * @return {IqrfServices} IQRF services
	 */
	public getIqrfServices(): IqrfServices {
		return new IqrfServices(this);
	}

	/**
	 * Returns Network services
	 * @return {NetworkServices} Network services
	 */
	public getNetworkServices(): NetworkServices {
		return new NetworkServices(this);
	}

	/**
	 * Returns Feature service
	 * @return {FeatureService} Feature service
	 */
	public getFeatureService(): FeatureService {
		return new FeatureService(this);
	}

	/**
	 * Returns Installation service
	 * @return {InstallationService} Installation service
	 */
	public getInstallationService(): InstallationService {
		return new InstallationService(this);
	}

	/**
	 * Returns Maintenance services
	 * @return {MaintenanceServices} Maintenance services
	 */
	public getMaintenanceServices(): MaintenanceServices {
		return new MaintenanceServices(this);
	}

	/**
	 * Returns OpenAPI specification service
	 * @return {OpenApiService} OpenAPI specification service
	 */
	public getOpenApiService(): OpenApiService {
		return new OpenApiService(this);
	}

	/**
	 * Returns Security services
	 * @return {SecurityServices} Security services
	 */
	public getSecurityServices(): SecurityServices {
		return new SecurityServices(this);
	}

	/**
	 * Returns System service service
	 * @return {ServiceService} System service service
	 */
	public getServiceService(): ServiceService {
		return new ServiceService(this);
	}

	/**
	 * Returns Version service
	 * @return {VersionService} Version service
	 */
	public getVersionService(): VersionService {
		return new VersionService(this);
	}

	/**
	 * Register a request interceptor that adds an Authorization header with API key or JWT token
	 */
	private registerRequestInterceptor(): void {
		this.axiosInstance.interceptors.request.use((config: InternalAxiosRequestConfig): InternalAxiosRequestConfig => {
			if (this.token) {
				config.headers.Authorization = `Bearer ${this.token}`;
			}
			return config;
		});
	}

	/**
	 * Add a request interceptor
	 * @param {Function} onFulfilled Fulfilled callback
	 * @param {Function} onRejected Rejected callback
	 * @param {AxiosInterceptorOptions} options Interceptor options
	 * @return {number} Interceptor ID
	 */
	public useRequestInterceptor(onFulfilled?: ((value: InternalAxiosRequestConfig) => InternalAxiosRequestConfig | Promise<InternalAxiosRequestConfig>) | null, onRejected?: ((error: any) => any) | null, options?: AxiosInterceptorOptions): number {
		return this.axiosInstance.interceptors.request.use(onFulfilled, onRejected, options);
	}

	/**
	 * Add a response interceptor
	 * @param {Function} onFulfilled Fulfilled callback
	 * @param {Function} onRejected Rejected callback
	 * @return {number} Interceptor ID
	 */
	public useResponseInterceptor(onFulfilled?: ((value: AxiosResponse) => AxiosResponse | Promise<AxiosResponse>) | null, onRejected?: ((error: any) => any) | null): number {
		return this.axiosInstance.interceptors.response.use(onFulfilled, onRejected);
	}

	/**
	 * Eject a request interceptor
	 * @param {number} interceptorId Interceptor ID
	 */
	public ejectRequestInterceptor(interceptorId: number): void {
		if (interceptorId === this.requestInterceptorId) {
			throw new Error('Cannot eject API client request interceptor.');
		}
		this.axiosInstance.interceptors.request.eject(interceptorId);
	}

	/**
	 * Eject a response interceptor
	 * @param {number} interceptorId Interceptor ID
	 */
	public ejectResponseInterceptor(interceptorId: number): void {
		this.axiosInstance.interceptors.response.eject(interceptorId);
	}

	/**
	 * Clear all request interceptors
	 */
	public clearRequestInterceptors(): void {
		this.axiosInstance.interceptors.request.clear();
		this.registerRequestInterceptor();
	}

	/**
	 * Clear all response interceptors
	 */
	public clearResponseInterceptors(): void {
		this.axiosInstance.interceptors.response.clear();
	}

}
