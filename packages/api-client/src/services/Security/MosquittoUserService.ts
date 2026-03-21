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
	type MosquittoUser,
	type MosquittoUserCreate,
	type MosquittoUserRaw,
} from '../../types/Security/MosquittoUser';
import { BaseService } from '../BaseService';


/**
 * Mosquitto user service
 */
export class MosquittoUserService extends BaseService {

	/**
	 * Retrieves list of mosquitto users
	 * @return {Promise<MosquittoUser[]>} List of mosquitto users
	 */
	public async list(): Promise<MosquittoUser[]> {
		const response: AxiosResponse<MosquittoUserRaw[]> =
			await this.axiosInstance.get('/security/mosquitto-users');
		return response.data.map((user: MosquittoUserRaw): MosquittoUser => this.deserializeUser(user));
	}

	/**
	 * Retrieves information about mosquitto user
	 * @param {number} id User ID
	 * @return {Promise<MosquittoUser>} Mosquitto user data
	 */
	public async get(id: number): Promise<MosquittoUser> {
		const response: AxiosResponse<MosquittoUserRaw> =
			await this.axiosInstance.get(`/security/mosquitto-users/${id.toString()}`);
		return this.deserializeUser(response.data);
	}

	/**
	 * Creates a new mosquitto user
	 * @param {MosquittoUserCreate} data Mosquitto user data
	 * @return {Promise<MosquittoUser>} Created mosquitto user
	 */
	public async create(data: MosquittoUserCreate): Promise<MosquittoUser> {
		const response: AxiosResponse<MosquittoUserRaw> =
			await this.axiosInstance.post('/security/mosquitto-users', data);
		return this.deserializeUser(response.data);
	}

	/**
	 * Block a mosquitto user
	 * @param {number} id User ID
	 */
	public async block(id: number): Promise<void> {
		await this.axiosInstance.post(`/security/mosquitto-users/${id.toString()}/block`);
	}

	/**
	 * Deserialize mosquttio user data
	 * @param {MosquittoUserRaw} user Mosquitto user raw data
	 * @return {MosquittoUser} Deserialized mosquitto user data
	 */
	private deserializeUser(user: MosquittoUserRaw): MosquittoUser {
		return {
			...user,
			createdAt: DateTime.fromISO(user.createdAt),
			blockedAt: user.blockedAt !== null ? DateTime.fromISO(user.blockedAt) : null,
		};
	}
}
