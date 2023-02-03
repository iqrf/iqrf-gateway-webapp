/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
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

import {IOperator} from '@/interfaces/Network/Mobile';

/**
 * Network operator object
 */
class NetworkOperator {

	/**
	 * Operator ID
	 */
	private readonly id: number;

	/**
	 * Operator name
	 */
	private readonly name: string;

	/**
	 * Operator APN
	 */
	private readonly apn: string;

	/**
	 * APN access username
	 */
	private readonly username: string;

	/**
	 * APN access password
	 */
	private readonly password: string;

	/**
	 * Constructor
	 * @param {IOperator} operator Network operator JSON
	 */
	constructor(operator: IOperator) {
		this.id = operator.id ?? 0;
		this.name = operator.name;
		this.apn = operator.apn;
		this.username = operator.username ?? '';
		this.password = operator.password ?? '';
	}

	/**
	 * Returns operator database ID
	 * @returns {number} Operator database ID
	 */
	public getId(): number {
		return this.id;
	}

	/**
	 * Returns operator name
	 * @returns {string} Operator name
	 */
	public getName(): string {
		return this.name;
	}

	/**
	 * Returns operator APN
	 * @returns {string} Operator APN
	 */
	public getApn(): string {
		return this.apn;
	}

	/**
	 * Returns APN username
	 * @returns {string} APN username
	 */
	public getUsername(): string {
		return this.username;
	}

	/**
	 * Returns APN password
	 * @returns {string} APN password
	 */
	public getPassword(): string {
		return this.password;
	}
}

export default NetworkOperator;
