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

import { type AxiosResponse } from 'axios';

import {
	type ApiKeyConfig,
	type ApiKeyCreated,
	type ApiKeyInfo,
} from '../../types/Security';
import { DateTimeUtils } from '../../utils';
import { BaseService } from '../BaseService';

/**
 * API key service
 */
export class ApiKeyService extends BaseService {

	/**
	 * Retrieves list of API keys
	 * @return {Promise<ApiKeyInfo[]>} List of API keys
	 */
	public async list(): Promise<ApiKeyInfo[]> {
		const response: AxiosResponse<ApiKeyInfo[]> =
			await this.axiosInstance.get('/security/apiKeys');
		return response.data.map((key: ApiKeyInfo): ApiKeyInfo => this.deserialize(key));
	}

	/**
	 * Creates new API key
	 * @param {ApiKeyConfig} key API key configuration to create
	 * @return {Promise<ApiKeyCreated>} Created API key
	 */
	public async create(key: ApiKeyConfig): Promise<ApiKeyCreated> {
		const response: AxiosResponse<ApiKeyCreated> =
			await this.axiosInstance.post('/security/apiKeys', this.serialize(key));
		return this.deserialize(response.data);
	}

	/**
	 * Retrieves information about the API key
	 * @param {number} id API key ID
	 * @return {Promise<ApiKeyInfo>} API key information
	 */
	public async get(id: number): Promise<ApiKeyInfo> {
		const response: AxiosResponse<ApiKeyInfo> =
			await this.axiosInstance.get(`/security/apiKeys/${id.toString()}`);
		return this.deserialize(response.data);
	}

	/**
	 * Updates the API key
	 * @param {number} id API key ID
	 * @param {ApiKeyConfig} config API key configuration to edit
	 */
	public async update(id: number, config: ApiKeyConfig): Promise<void> {
		await this.axiosInstance.put(`/security/apiKeys/${id.toString()}`, this.serialize(config));
	}

	/**
	 * Deletes the API key
	 * @param {number} id API key ID
	 */
	public async delete(id: number): Promise<void> {
		await this.axiosInstance.delete(`/security/apiKeys/${id.toString()}`);
	}

	/**
	 * Deserializes API key
	 * @template {ApiKeyConfig|ApiKeyInfo|ApiKeyCreated} T API key type
	 * @param {T} key API key to deserialize
	 * @return {T} Deserialized API key
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
	 */
	private serialize(key: ApiKeyConfig): ApiKeyConfig {
		// @ts-ignore
		key.expiration = DateTimeUtils.serialize(key.expiration);
		return key;
	}


}
