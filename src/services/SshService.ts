/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
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
import axios, {AxiosResponse} from 'axios';
import {authorizationHeader} from '@/helpers/authorizationHeader';
import {ISshInput} from '@/interfaces/Core/SshKey';

/**
 * SSH service
 */
class SshService {

	/**
	 * Lists SSH key types
	 */
	listKeyTypes(): Promise<AxiosResponse> {
		return axios.get('gateway/ssh/keyTypes', {headers: authorizationHeader()});
	}

	/**
	 * Lists SSH public keys
	 */
	listKeys(): Promise<AxiosResponse> {
		return axios.get('gateway/ssh/keys', {headers: authorizationHeader()});
	}

	/**
	 * Retrieves SSH public key
	 * @param {number} id Key ID
	 */
	getKey(id: number): Promise<AxiosResponse> {
		return axios.get('gateway/ssh/keys/' + id, {headers: authorizationHeader()});
	}

	/**
	 * Removes SSH public key
	 * @param {number} id Key ID
	 */
	deleteKey(id: number): Promise<AxiosResponse> {
		return axios.delete('gateway/ssh/keys/' + id, {headers: authorizationHeader()});
	}

	/**
	 * Saves SSH keys
	 * @param {Array<ISshInput>} keys SSh keys
	 */
	saveSshKeys(keys: Array<ISshInput>): Promise<AxiosResponse> {
		return axios.post('gateway/ssh/keys', keys, {headers: authorizationHeader()});
	}
}

export default new SshService();
