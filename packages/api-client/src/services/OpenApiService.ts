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
	 * Schema path regular expression
	 */
	public readonly schemaPathRegExp: RegExp = /https:\/\/apidocs\.iqrf\.org\/openapi\/iqrf-gateway-webapp\/schemas\/((features\/|definitions\/)?(\w*))\.json/g;

	/**
	 * Retrieve OpenAPI specification
	 * @return {Promise<OpenAPI3>} OpenAPI specification
	 */
	public async getSpecification(): Promise<OpenAPI3> {
		const response: AxiosResponse<OpenAPI3> =
			await this.axiosInstance.get('/openapi');
		const spec: OpenAPI3 = JSON.parse(this.fixSchemaUrls(JSON.stringify(response.data))) as OpenAPI3;
		// @ts-ignore Ignore missing description and variable properties in OpenAPI v3.x server object
		spec.servers = [{ url: this.getBaseUrl() }];
		return spec;
	}

	/**
	 * Fixes the OpenAPI specification reference URLs
	 * @param {string} response OpenAPI specification reference URLs
	 * @return {string} Fixed OpenAPI specification reference URLs
	 */
	public fixSchemaUrls(response: string): string {
		const replacement: string = `${this.getBaseUrl()}/openapi/schemas/$1`;
		return response.replaceAll(this.schemaPathRegExp, replacement);
	}

	/**
	 * Retrieves the base URL of the REST API
	 * @return {string} Base URL of the REST API
	 * @private
	 */
	private getBaseUrl(): string {
		return (this.apiClient.getAxiosInstance().defaults.baseURL ?? '/api/v0/').replace(/\/$/, '');
	}

}
