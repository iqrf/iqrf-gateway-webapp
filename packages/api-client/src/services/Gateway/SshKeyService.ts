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

import {
	type SshKeyCreate,
	type SshKeyInfo,
	type SshKeyInfoRaw,
} from '../../types/Gateway';
import { DateTimeUtils } from '../../utils';
import { BaseService } from '../BaseService';

/**
 * SSH key service
 */
export class SshKeyService extends BaseService {

	/**
	 * Fetches all supported SSH key types
	 * @return {Promise<string[]>} Supported SSH key types
	 */
	public async fetchKeyTypes(): Promise<string[]> {
		const response: AxiosResponse<string[]> =
			await this.axiosInstance.get('/gateway/ssh/keyTypes');
		return response.data;
	}

	/**
	 * Fetches all SSH keys
	 * @return {Promise<SshKeyInfo[]>} List of SSH keys
	 */
	public async list(): Promise<SshKeyInfo[]> {
		const response: AxiosResponse<SshKeyInfoRaw[]> =
			await this.axiosInstance.get('/gateway/ssh/keys');
		return response.data.map((key: SshKeyInfoRaw): SshKeyInfo => this.deserializeInfo(key));
	}

	/**
	 * Retrieves SSH public key
	 * @param {number} id Key ID
	 * @return {Promise<SshKeyInfo>} SSH key
	 */
	public async getKey(id: number): Promise<SshKeyInfo> {
		const response: AxiosResponse<SshKeyInfoRaw> =
			await this.axiosInstance.get(`/gateway/ssh/keys/${id.toString()}`);
		return this.deserializeInfo(response.data);
	}

	/**
	 * Removes SSH public key
	 * @param {number} id Key ID
	 */
	public async deleteKey(id: number): Promise<void> {
		await this.axiosInstance.delete(`/gateway/ssh/keys/${id.toString()}`);
	}

	/**
	 * Saves SSH keys
	 * @param {SshKeyCreate[]} keys SSh keys
	 */
	public async createSshKeys(keys: SshKeyCreate[]): Promise<void> {
		await this.axiosInstance.post('/gateway/ssh/keys', keys);
	}

	/**
	 * Deserializes SSH key info
	 * @param {SshKeyInfoRaw} raw Raw SSH key info
	 * @return {SshKeyInfo} Deserialized SSH key info
	 */
	private deserializeInfo(raw: SshKeyInfoRaw): SshKeyInfo {
		return {
			...raw,
			createdAt: DateTimeUtils.deserialize(raw.createdAt),
		};
	}

}
