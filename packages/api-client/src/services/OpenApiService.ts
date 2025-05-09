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
import { type OpenAPI3 } from 'openapi-typescript';

import { BaseService } from './BaseService';

/**
 * OpenAPI specification service
 */
export class OpenApiService extends BaseService {

	/**
	 * Retrieve OpenAPI specification
	 * @param {string} baseUrl REST API base URL
	 * @return {Promise<OpenAPI3>} OpenAPI specification
	 */
	public async getSpecification(baseUrl: string = ''): Promise<OpenAPI3> {
		const response: AxiosResponse<OpenAPI3> =
			await this.axiosInstance.get('/openapi');
		const regExp: RegExp = /https:\/\/apidocs\.iqrf\.org\/iqrf-gateway-webapp-api\/schemas\/(\w*)\.json/g;
		const replacement: string = `${baseUrl}/openapi/schemas/$1`;
		const spec: OpenAPI3 = JSON.parse(JSON.stringify(response.data).replaceAll(regExp, replacement)) as OpenAPI3;
		// @ts-ignore Ignore missing description and variable properties in OpenAPI v3.x server object
		spec.servers = [{ url: baseUrl }];
		return spec;
	}

}
