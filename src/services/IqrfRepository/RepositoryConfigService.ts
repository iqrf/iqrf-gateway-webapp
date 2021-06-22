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
import {authorizationHeader} from '../../helpers/authorizationHeader';

import axios, {AxiosResponse} from 'axios';
import {IIqrfRepositoryConfig} from '../../interfaces/iqrfRepository';

/**
 * Repository configuration service
 */
class RepositoryConfigService {

	/**
	 * Retrieves IQRF repository configuration
	 */
	get(): Promise<AxiosResponse> {
		return axios.get('iqrf/repository/config', {headers: authorizationHeader()});
	}

	/**
	 * Saves IQRF repository configuration
	 * @param config IQRF repository configuration
	 */
	save(config: IIqrfRepositoryConfig): Promise<AxiosResponse> {
		return axios.put('iqrf/repository/config', config, {headers: authorizationHeader()});
	}
}

export default new RepositoryConfigService();
 