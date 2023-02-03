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
import axios, {AxiosResponse} from 'axios';
import {authorizationHeader} from '@/helpers/authorizationHeader';

/**
 * API key service
 */
class ApiKeyService {
	/**
	 * Retrieves list of API keys
	 */
	getApiKeys(): Promise<AxiosResponse> {
		return axios.get('apiKeys', {headers: authorizationHeader()});
	}

	/**
	 * Adds a new API key
	 * @param keyData new API key metadata
	 */
	addApiKey(keyData: Record<string, unknown>): Promise<AxiosResponse> {
		return axios.post('apiKeys', keyData, {headers: authorizationHeader()});
	}

	/**
	 * Retrieves API key specified by ID
	 * @param keyId API key ID
	 */
	getApiKey(keyId: number): Promise<AxiosResponse> {
		return axios.get('apiKeys/' + keyId, {headers: authorizationHeader()});
	}

	/**
	 * Updates metadata of API key specified by ID
	 * @param keyId API key ID
	 * @param keyData API key metadata
	 */
	editApiKey(keyId: number, keyData: Record<string, unknown>): Promise<AxiosResponse> {
		delete keyData.id;
		return axios.put('apiKeys/' + keyId, keyData, {headers: authorizationHeader()});
	}

	/**
	 * Removes API key specified by ID
	 * @param keyId API key ID
	 */
	deleteApiKey(keyId: number): Promise<AxiosResponse> {
		return axios.delete('apiKeys/' + keyId, {headers: authorizationHeader()});
	}
}

export default new ApiKeyService();
