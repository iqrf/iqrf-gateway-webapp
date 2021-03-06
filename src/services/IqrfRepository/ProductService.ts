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
import IqrfRepositoryConfigService from './IqrfRepositoryConfigService';
import {IIqrfRepositoryConfig} from '../../interfaces/iqrfRepository';

class ProductService {

	/**
	 * Retrieves the product by its HWPID
	 * @param hwpid Product HWPID
	 */
	public async get(hwpid: number): Promise<AxiosResponse> {
		let baseUrl = 'https://repository.iqrfalliance.org/api';
		await IqrfRepositoryConfigService.get()
			.then((config: IIqrfRepositoryConfig) => (baseUrl = config.apiEndpoint));
		return axios.get(baseUrl + '/products/' + hwpid);
	}
}

export default new ProductService();
