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
import {authorizationHeader} from '@/helpers/authorizationHeader';

import axios, {AxiosResponse} from 'axios';
import {IOperator} from '@/interfaces/Network/Mobile';

/**
 * Network operator service
 */
class NetworkOperatorService {
	/**
	 * Retrieves network operators from database
	 */
	getOperators(): Promise<AxiosResponse> {
		return axios.get('network/operators', {headers: authorizationHeader()});
	}

	/**
	 * Retrieves a network operator specified by database entry ID
	 * @param id Network operator ID
	 */
	getOperator(id: number): Promise<AxiosResponse> {
		return axios.get('network/operators/' +  id, {headers: authorizationHeader()});
	}

	/**
	 * Adds a new network operator to database
	 * @param {IOperator} operator Network operator
	 */
	addOperator(operator: IOperator): Promise<AxiosResponse> {
		return axios.post('network/operators', operator, {headers: authorizationHeader()});
	}

	/**
	 * Edits an existing network operator in database
	 * @param id Operator ID
	 * @param operator Operator information
	 */
	editOperator(id: number, operator: IOperator): Promise<AxiosResponse> {
		return axios.put('network/operators/' + id, operator, {headers: authorizationHeader()});
	}

	/**
	 * Deletes an existing network operator from database
	 * @param id Operator ID
	 */
	deleteOperator(id: number): Promise<AxiosResponse> {
		return axios.delete('network/operators/' + id, {headers: authorizationHeader()});
	}
}

export default new NetworkOperatorService();
