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

import { type AxiosInstance } from 'axios';

import { type Client } from '../client';

/**
 * Base service
 */
export abstract class BaseService {

	/**
	 * @property {Client} apiClient API client
	 * @protected
	 */
	protected readonly apiClient: Client;

	/**
	 * @property {AxiosInstance} axiosInstance Axios instance
	 * @protected
	 */
	protected readonly axiosInstance: AxiosInstance;

	/**
	 * Constructor
	 * @param {Client} apiClient API client
	 */
	public constructor(apiClient: Client) {
		this.apiClient = apiClient;
		this.axiosInstance = this.apiClient.getAxiosInstance();
	}

}
