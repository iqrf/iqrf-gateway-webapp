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

import { type MobileOperator } from '../../types/Network';
import { BaseService } from '../BaseService';

/**
 * Mobile operator service
 */
export class MobileOperatorService extends BaseService {

	/**
	 * Retrieves list of mobile operators
	 * @return {Promise<MobileOperator[]>} List of mobile operators
	 */
	public async list(): Promise<MobileOperator[]> {
		const response: AxiosResponse<MobileOperator[]> =
			await this.axiosInstance.get('/network/cellular/operators');
		return response.data.map((operator: MobileOperator): MobileOperator => this.deserialize(operator));
	}

	/**
	 * Creates a new mobile operator
	 * @param {MobileOperator} operator Mobile operator to create
	 */
	public async create(operator: MobileOperator): Promise<void> {
		await this.axiosInstance.post('/network/cellular/operators', this.deserialize(operator));
	}

	/**
	 * Retrieves the mobile operator
	 * @param {number} id Mobile operator ID
	 * @return {Promise<MobileOperator>} Mobile operator
	 */
	public async get(id: number): Promise<MobileOperator> {
		const response: AxiosResponse<MobileOperator> =
			await this.axiosInstance.get(`/network/cellular/operators/${id.toString()}`);
		return this.deserialize(response.data);
	}

	/**
	 * Updates the mobile operator
	 * @param {number} id Mobile operator ID
	 * @param {MobileOperator} operator Mobile operator to edit
	 */
	public async update(id: number, operator: MobileOperator): Promise<void> {
		await this.axiosInstance.put(`/network/cellular/operators/${id.toString()}`, this.serialize(operator));
	}

	/**
	 * Deletes the mobile operator
	 * @param {number} id Mobile operator ID
	 */
	public async delete(id: number): Promise<void> {
		await this.axiosInstance.delete(`/network/cellular/operators/${id.toString()}`);
	}

	/**
	 * Deserializes mobile operator from API
	 * @param {MobileOperator} operator Mobile operator to deserialize
	 * @return {MobileOperator} Deserialized mobile operator
	 */
	private deserialize(operator: MobileOperator): MobileOperator {
		return {
			...operator,
			username: operator.username ?? '',
			password: operator.password ?? '',
		};
	}

	/**
	 * Serializes mobile operator for API
	 * @param {MobileOperator} operator Mobile operator to serialize
	 * @return {MobileOperator} Serialized mobile operator
	 */
	private serialize(operator: MobileOperator): MobileOperator {
		delete operator.id;
		if (operator.username?.length === 0) {
			delete operator.username;
		}
		if (operator.password?.length === 0) {
			delete operator.password;
		}
		return operator;
	}

}
