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
import {type AxiosResponse} from 'axios';

import {BaseService} from '../BaseService';
import type {SshKeyCreate, SshKeyInfo, SshKeyInfoRaw} from '../../types/Gateway';
import {DateTimeUtils} from '../../utils';

/**
 * SSH key service
 */
export class SshKeyService extends BaseService {

	/**
	 * Fetches all supported SSH key types
	 * @return {Promise<string[]>} Supported SSH key types
	 */
	public fetchKeyTypes(): Promise<string[]> {
		return this.axiosInstance.get('/gateway/ssh/keyTypes')
			.then((response: AxiosResponse<string[]>): string[] => response.data);
	}

	/**
	 * Fetches all SSH keys
	 * @return {Promise<SshKeyInfo[]>} List of SSH keys
	 */
	public list(): Promise<SshKeyInfo[]> {
		return this.axiosInstance.get('/gateway/ssh/keys')
			.then((response: AxiosResponse<SshKeyInfoRaw[]>): SshKeyInfo[] =>
				response.data.map((key: SshKeyInfoRaw): SshKeyInfo => this.deserializeInfo(key)),
			);
	}

	/**
	 * Retrieves SSH public key
	 * @param {number} id Key ID
	 * @return {Promise<SshKeyInfo>} SSH key
	 */
	public getKey(id: number): Promise<SshKeyInfo> {
		return this.axiosInstance.get('gateway/ssh/keys/' + id)
			.then((response: AxiosResponse<SshKeyInfoRaw>): SshKeyInfo =>
				this.deserializeInfo(response.data),
			);
	}

	/**
	 * Removes SSH public key
	 * @param {number} id Key ID
	 */
	public deleteKey(id: number): Promise<void> {
		return this.axiosInstance.get('gateway/ssh/keys/' + id)
			.then((): void => {return;});
	}

	/**
	 * Saves SSH keys
	 * @param {SshKeyCreate[]} keys SSh keys
	 */
	public createSshKeys(keys: SshKeyCreate[]): Promise<void> {
		return this.axiosInstance.post('gateway/ssh/keys', keys)
			.then((): void => {return;});
	}

	/**
	 * Deserializes SSH key info
	 * @param {SshKeyInfoRaw} raw Raw SSH key info
	 * @return {SshKeyInfo} Deserialized SSH key info
	 */
	private deserializeInfo(raw: SshKeyInfoRaw): SshKeyInfo {
		return {
			...raw,
			createdAt: DateTimeUtils.deserialize(raw.createdAt)!,
		};
	}

}
