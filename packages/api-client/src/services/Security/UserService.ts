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
	type EmailSentResponse,
	type UserCreate,
	type UserEdit,
	type UserInfo,
} from '../../types';
import { UserUtils } from '../../utils';
import { BaseService } from '../BaseService';

/**
 * User service
 */
export class UserService extends BaseService {

	/**
	 * Retrieve a list of all users
	 * @return {Promise<UserInfo[]>} List of all users
	 */
	public async list(): Promise<UserInfo[]> {
		const response: AxiosResponse<UserInfo[]> =
			await this.axiosInstance.get('/security/users');
		return response.data.map((user: UserInfo) => UserUtils.deserialize(user));
	}

	/**
	 * Blocks the user
	 * @param {number} id ID of user to block
	 */
	public async block(id: number): Promise<void> {
		await this.axiosInstance.post(`/security/users/${id.toString()}/block`);
	}

	/**
	 * Unblocks the user
	 * @param {number} id ID of user to unblock
	 */
	public async unblock(id: number): Promise<void> {
		await this.axiosInstance.post(`/security/users/${id.toString()}/unblock`);
	}

	/**
	 * Create a new user
	 * @param {UserCreate} user User to create
	 * @return {Promise<EmailSentResponse>} Email sent response
	 */
	public async create(user: UserCreate): Promise<EmailSentResponse> {
		const response: AxiosResponse<EmailSentResponse> =
			await this.axiosInstance.post('/security/users', UserUtils.serialize(user));
		return response.data;
	}

	/**
	 * Retrieve information about the user
	 * @param {number} id User ID
	 * @return {Promise<UserInfo>} User information
	 */
	public async get(id: number): Promise<UserInfo> {
		const response: AxiosResponse<UserInfo> =
			await this.axiosInstance.get(`/security/users/${id.toString()}`);
		return UserUtils.deserialize(response.data);
	}

	/**
	 * Update the user
	 * @param {number} id User ID
	 * @param {UserEdit} user User to edit
	 * @return {Promise<EmailSentResponse>} Email sent response
	 */
	public async update(id: number, user: UserEdit): Promise<EmailSentResponse> {
		const response: AxiosResponse<EmailSentResponse> =
			await this.axiosInstance.put(`/security/users/${id.toString()}`, UserUtils.serialize(user));
		return response.data;
	}

	/**
	 * Delete the user
	 * @param {number} id User ID
	 */
	public async delete(id: number): Promise<void> {
		await this.axiosInstance.delete(`/security/users/${id.toString()}`);
	}

	/**
	 * Resend the verification email
	 * @param {number} id User ID
	 */
	public async resendVerificationEmail(id: number): Promise<void> {
		await this.axiosInstance.post(`/security/users/${id.toString()}/resendVerification`);
	}

}
