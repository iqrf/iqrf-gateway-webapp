/**
 * Copyright 2023-2026 MICRORISC s.r.o.
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
import { DateTime } from 'luxon';

import {
	type DaemonApiTokenCreate,
	type DaemonApiTokenCreated,
	type DaemonApiTokenInfo,
	type DaemonApiTokenInfoRaw,
} from '../../types/Security/DaemonApiToken';
import { BaseService } from '../BaseService';

/**
 * Daemon API access token service
 */
export class DaemonApiTokenService extends BaseService {

	/**
	 * Retrieves list of Daemon API access tokens
	 * @return {Promise<DaemonApiTokenInfo[]>} List of Daemon API ccess tokens
	 */
	public async list(): Promise<DaemonApiTokenInfo[]> {
		const response: AxiosResponse<DaemonApiTokenInfoRaw[]> =
			await this.axiosInstance.get('/security/daemon-access-tokens');
		return response.data.map((token: DaemonApiTokenInfoRaw): DaemonApiTokenInfo => this.deserializeInfo(token));
	}

	/**
	 * Retrieves information about Daemon API access token
	 * @param {number} id Token ID
	 * @return {Promise<DaemonApiTokenInfo>} Daemon API access token information
	 */
	public async get(id: number): Promise<DaemonApiTokenInfo> {
		const response: AxiosResponse<DaemonApiTokenInfoRaw> =
			await this.axiosInstance.get(`/security/daemon-access-tokens/${id.toString()}`);
		return this.deserializeInfo(response.data);
	}

	/**
	 * Creates a new Daemon API access token
	 * @param {DaemonApiTokenCreate} data Access token data
	 * @return {Promise<DaemonApiTokenCreated>} Created Daemon API access token
	 */
	public async create(data: DaemonApiTokenCreate): Promise<DaemonApiTokenCreated> {
		const response: AxiosResponse<DaemonApiTokenCreated> =
			await this.axiosInstance.post('/security/daemon-access-tokens', data);
		return response.data;
	}

	/**
	 * Revoek a Daemon API access token
	 * @param {number} id Token ID
	 */
	public async revoke(id: number): Promise<void> {
		await this.axiosInstance.post(`/security/daemon-access-tokens/${id.toString()}/revoke`);
	}

	/**
	 * Rotate a Daemon API access token
	 * @param {number} id Token ID
	 * @return {Promise<DaemonApiTokenCreated>} Rotated Daemon API access token
	 */
	public async rotate(id: number): Promise<DaemonApiTokenCreated> {
		const response: AxiosResponse<DaemonApiTokenCreated> =
			await this.axiosInstance.post(`/security/daemon-access-tokens/${id.toString()}/rotate`);
		return response.data;
	}

	/**
	 * Deserializes Daemon API access token informaton
	 * @param {DaemonApiTokenInfoRaw} token Raw token information
	 * @return {DaemonApiTokenInfo} Deserialized token information
	 */
	private deserializeInfo(token: DaemonApiTokenInfoRaw): DaemonApiTokenInfo {
		return {
			...token,
			created_at: DateTime.fromISO(token.created_at),
			expires_at: DateTime.fromISO(token.expires_at),
			invalidated_at: token.invalidated_at !== null ? DateTime.fromISO(token.invalidated_at) : null,
		};
	}
}
