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

import axios, {
	type AxiosInstance,
	type AxiosInterceptorOptions,
	type AxiosRequestConfig,
	type AxiosResponse,
	type InternalAxiosRequestConfig,
} from 'axios';

import {version as clientVersion} from '../package.json';
import {
	AccountService,
	ApiKeyService,
	AuthenticationService, CloudServices, ConfigServices,
	DpaMacrosService,
	FeatureService,
	InstallationService,
	IqrfRepositoryService,
	MailerService,
	ServiceService,
	UserService,
	VersionService,
} from './services';
import {IqrfServices} from './services/Iqrf';

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
 * 	baseURL: 'https://iqrf-gw.exaple.org/api/',
 * };
 * const client = new Client({config});
 * ```
 *
 * ## Instantiate with custom Axios instance **advanced**
 * ```typescript
 * const config: AxiosRequestConfig = {
 * 	baseURL: 'https://iqrf-gw.exaple.org/api/',
 * }
 * const axiosInstance = axios.create(config);
 * const client = new Client({axiosInstance});
 */
export class Client {

	/**
	 * @property {AxiosInstance} axiosInstance Axios instance
	 * @private
	 */
	private axiosInstance: AxiosInstance;

	/**
	 * @property {AxiosRequestConfig} defaultAxiosConfig Default Axios instance configuration
	 * @private
	 */
	private readonly defaultAxiosConfig: AxiosRequestConfig = {
		/** IQRF Gateway Webapp API base URL */
		baseURL: '/api/',
		/** Timeout in milliseconds */
		timeout: 30_000,
		/** Headers */
		headers: {
			/** User agent */
			'User-Agent': `iqrf-gateway-webapp-js-client_v${clientVersion}`,
		},
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
	public constructor({axiosInstance, config, token}: ClientOptions = {}) {
		if (axiosInstance && config) {
			throw new Error('Cannot instantiate Client with both axiosInstance and config.');
		}
		if (axiosInstance) {
			this.axiosInstance = axiosInstance;
			// @ts-ignore
			this.axiosInstance.defaults.headers = {
				...this.defaultAxiosConfig.headers,
				...this.axiosInstance.defaults.headers,
			};
		} else if (config) {
			config = {
				...this.defaultAxiosConfig,
				...config,
			};
			config.headers = {
				...this.defaultAxiosConfig.headers,
				...config.headers,
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
	 * Returns API key service
	 * @return {ApiKeyService} API key service
	 */
	public getApiKeyService(): ApiKeyService {
		return new ApiKeyService(this);
	}

	/**
	 * Returns Authentication service
	 * @return {AuthenticationService} Authentication service
	 */
	public getAuthenticationService(): AuthenticationService {
		return new AuthenticationService(this);
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
	 * Returns IQRF services
	 * @return {IqrfServices} IQRF services
	 */
	public getIqrfServices(): IqrfServices {
		return new IqrfServices(this);
	}

	/**
	 * Returns DPA macros service
	 * @return {DpaMacrosService} DPA macros service
	 */
	public getDpaMacrosService(): DpaMacrosService {
		return new DpaMacrosService(this);
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
	 * Returns IQRF Repository configuration service
	 * @return {IqrfRepositoryService} IQRF Repository configuration service
	 */
	public getIqrfRepositoryService(): IqrfRepositoryService {
		return new IqrfRepositoryService(this);
	}

	/**
	 * Returns Mailer configuration service
	 * @return {MailerService} Mailer configuration service
	 */
	public getMailerService(): MailerService {
		return new MailerService(this);
	}

	/**
	 * Returns System service service
	 * @return {ServiceService} System service service
	 */
	public getServiceService(): ServiceService {
		return new ServiceService(this);
	}

	/**
	 * Returns User service
	 * @return {UserService} User service
	 */
	public getUserService(): UserService {
		return new UserService(this);
	}

	/**
	 * Returns Version service
	 * @return {VersionService} Version service
	 */
	public getVersionService(): VersionService {
		return new VersionService(this);
	}

	/**
	 * Registers a request interceptor that adds an Authorization header with API key or JWT token
	 * @private
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
	 * Adds a request interceptor
	 * @param {function} onFulfilled Fulfilled callback
	 * @param {function} onRejected Rejected callback
	 * @param {AxiosInterceptorOptions} options Interceptor options
	 * @return {number} Interceptor ID
	 */
	public useRequestInterceptor(onFulfilled?: ((value: InternalAxiosRequestConfig) => InternalAxiosRequestConfig | Promise<InternalAxiosRequestConfig>) | null, onRejected?: ((error: any) => any) | null, options?: AxiosInterceptorOptions): number {
		return this.axiosInstance.interceptors.request.use(onFulfilled, onRejected, options);
	}

	/**
	 * Adds a response interceptor
	 * @param {function} onFulfilled Fulfilled callback
	 * @param {function} onRejected Rejected callback
	 * @param {AxiosInterceptorOptions} options Interceptor options
	 * @return {number} Interceptor ID
	 */
	public useResponseInterceptor(onFulfilled?: ((value: AxiosResponse) => AxiosResponse | Promise<AxiosResponse>) | null, onRejected?: ((error: any) => any) | null, options?: AxiosInterceptorOptions): number {
		return this.axiosInstance.interceptors.response.use(onFulfilled, onRejected, options);
	}

	/**
	 * Ejects a request interceptor
	 * @param {number} interceptorId Interceptor ID
	 */
	public ejectRequestInterceptor(interceptorId: number): void {
		if (interceptorId === this.requestInterceptorId) {
			throw new Error('Cannot eject API client request interceptor.');
		}
		this.axiosInstance.interceptors.request.eject(interceptorId);
	}

	/**
	 * Ejects a response interceptor
	 * @param {number} interceptorId Interceptor ID
	 */
	public ejectResponseInterceptor(interceptorId: number): void {
		this.axiosInstance.interceptors.response.eject(interceptorId);
	}

	/**
	 * Clears all request interceptors
	 */
	public clearRequestInterceptors(): void {
		this.axiosInstance.interceptors.request.clear();
		this.registerRequestInterceptor();
	}

	/**
	 * Clears all response interceptors
	 */
	public clearResponseInterceptors(): void {
		this.axiosInstance.interceptors.response.clear();
	}

}
