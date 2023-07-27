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

import {IqrfRepositoryConfig} from '@iqrf/iqrf-gateway-webapp-client';
import {Client, ClientCredentials} from '@iqrf/iqrf-repository-client';

import store from '@/store';
import {useApiClient} from '@/services/ApiClient';

/**
 * Creates IQRF Repository API client instance
 * @return {Promise<Client>} IQRF Repository API client instance
 * @todo Read base URL from the configuration file
 */
export const useRepositoryClient = async (): Promise<Client> => {
	let baseUrl = 'https://repository.iqrfalliance.org/api/';
	let credentials: ClientCredentials = {
		username: null,
		password: null,
	};
	let config = store.getters['repository/configuration'];
	if (!config) {
		config = await useApiClient().getIqrfRepositoryService().getConfig()
			.then((repositoryConfig: IqrfRepositoryConfig) => repositoryConfig);
	}
	if (!config) {
		baseUrl = config.apiEndpoint;
		credentials = config.credentials;
	}
	return new Client({
		config: {
			baseURL: baseUrl,
		},
		credentials: credentials,
	});
};
