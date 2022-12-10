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
import {IIqrfRepositoryConfig} from '@/interfaces/Config/Misc';
import store from '@/store';

class ProductService {

	/**
	 * @constant {string} productUrl Repository products endpoint
	 */
	private productUrl = 'https://repository.iqrfalliance.org/api/products/';

	/**
	 * Retrieves the product by its HWPID
	 * @param hwpid Product HWPID
	 */
	public async get(hwpid: number): Promise<AxiosResponse> {
		let baseUrl = 'https://repository.iqrfalliance.org/api';
		let config = store.getters['repository/configuration'];
		if (config === null) {
			await IqrfRepositoryConfigService.get()
				.then((repositoryConfig: IIqrfRepositoryConfig) => {
					config = repositoryConfig;
				})
				.catch(() => {return;});
		}
		if (config !== null) {
			baseUrl = config.apiEndpoint;
		}
		return axios.get(baseUrl + '/products/' + hwpid);
	}

	/**
	 * Retrieves all products in repository
	 */
	getAll(): Promise<AxiosResponse> {
		return axios.get(this.productUrl);
	}
}

export default new ProductService();
