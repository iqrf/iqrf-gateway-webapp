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

import { type AxiosResponse } from 'axios';

import { type ApiKeyConfig, type ApiKeyCreated, type ApiKeyInfo } from '../types';
import { DateTimeUtils } from '../utils';

import { BaseService } from './BaseService';

/**
 * API key service
 */
export class ApiKeyService extends BaseService {

	/**
	 * Fetches list of API keys
	 * @return {Promise<ApiKeyInfo[]>} List of API keys
	 */
	public list(): Promise<ApiKeyInfo[]> {
		return this.axiosInstance.get('/apiKeys')
			.then((response: AxiosResponse<ApiKeyInfo[]>): ApiKeyInfo[] => response.data.map((key: ApiKeyInfo): ApiKeyInfo => this.deserialize(key)));
	}

	/**
	 * Creates new API key
	 * @param {ApiKeyConfig} key API key configuration to create
	 * @return {Promise<ApiKeyCreated>} Created API key
	 */
	public create(key: ApiKeyConfig): Promise<ApiKeyCreated> {
		return this.axiosInstance.post('/apiKeys', this.serialize(key))
			.then((response: AxiosResponse<ApiKeyCreated>): ApiKeyCreated => this.deserialize(response.data));
	}

	/**
	 * Fetches information about the API key
	 * @param {number} id API key ID
	 * @return {Promise<ApiKeyInfo>} API key information
	 */
	public fetch(id: number): Promise<ApiKeyInfo> {
		return this.axiosInstance.get(`/apiKeys/${id}`)
			.then((response: AxiosResponse<ApiKeyInfo>): ApiKeyInfo => this.deserialize(response.data));
	}

	/**
	 * Edits the API key
	 * @param {number} id API key ID
	 * @param {ApiKeyConfig} config API key configuration to edit
	 */
	public edit(id: number, config: ApiKeyConfig): Promise<void> {
		return this.axiosInstance.put(`/apiKeys/${id}`, this.serialize(config))
			.then((): void => {return;});
	}

	/**
	 * Deletes the API key
	 * @param {number} id API key ID
	 */
	public delete(id: number): Promise<void> {
		return this.axiosInstance.delete(`/apiKeys/${id}`)
			.then((): void => {return;});
	}

	/**
	 * Deserializes API key
	 * @template {ApiKeyConfig|ApiKeyInfo|ApiKeyCreated} T API key type
	 * @param {T} key API key to deserialize
	 * @return {T} Deserialized API key
	 * @private
	 */
	private deserialize<T extends ApiKeyConfig|ApiKeyInfo|ApiKeyCreated>(key: T): T {
		const expiration: unknown = key.expiration;
		key.expiration = DateTimeUtils.deserialize(expiration as string);
		return key;
	}

	/**
	 * Serializes API key
	 * @param {ApiKeyConfig} key API key to serialize
	 * @return {ApiKeyConfig} Serialized API key
	 * @private
	 */
	private serialize(key: ApiKeyConfig): ApiKeyConfig {
		// @ts-ignore
		key.expiration = DateTimeUtils.serialize(key.expiration);
		return key;
	}


}
