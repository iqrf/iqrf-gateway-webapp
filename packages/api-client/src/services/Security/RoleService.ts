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
	type RoleConfig,
	type RoleInfo,
} from '../../types/Security';
import { BaseService } from '../BaseService';

/**
 * Role service
 */
export class RoleService extends BaseService {

	/**
	 * Retrieves list of roles
	 * @return {Promise<RoleInfo[]>} List of roles
	 */
	public async list(): Promise<RoleInfo[]> {
		const response: AxiosResponse<RoleInfo[]> =
			await this.axiosInstance.get('/roles');
		return response.data;
	}

	/**
	 * Creates a new role
	 * @param {RoleConfig} role Role configuration to create
	 * @return {Promise<RoleInfo>} Created role
	 */
	public async create(role: RoleConfig): Promise<RoleInfo> {
		const response: AxiosResponse<RoleInfo> =
			await this.axiosInstance.post('/roles', role);
		return response.data;
	}

	/**
	 * Retrieves information about the role
	 * @param {number} id Role ID
	 * @return {Promise<RoleInfo>} Role information
	 */
	public async get(id: number): Promise<RoleInfo> {
		const response: AxiosResponse<RoleInfo> =
			await this.axiosInstance.get(`/roles/${id.toString()}`);
		return response.data;
	}

	/**
	 * Updates the role
	 * @param {number} id Role ID
	 * @param {RoleConfig} role Role configuration to edit
	 * @return {Promise<RoleInfo>} Updated role
	 */
	public async update(id: number, role: RoleConfig): Promise<RoleInfo> {
		const response: AxiosResponse<RoleInfo> =
			await this.axiosInstance.put(`/roles/${id.toString()}`, role);
		return response.data;
	}

	/**
	 * Deletes the role
	 * @param {number} id Role ID
	 */
	public async delete(id: number): Promise<void> {
		await this.axiosInstance.delete(`/roles/${id.toString()}`);
	}

}
